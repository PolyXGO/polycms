<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Ecommerce\Order;
use App\Services\Ecommerce\InventoryLedgerService;
use App\Services\Ecommerce\OrderInventorySyncService;
use App\Services\Ecommerce\OrderRefundService;
use App\Services\Ecommerce\InvoiceService;
use App\Services\SettingsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function __construct(
        protected OrderRefundService $orderRefundService,
        protected InventoryLedgerService $inventoryLedgerService,
        protected OrderInventorySyncService $orderInventorySyncService,
        protected InvoiceService $invoiceService,
        protected SettingsService $settings
    ) {}

    /**
     * Display a listing of orders.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Order::with(['user', 'items.product.media']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('user_id')) {
            if ($request->user_id === 'me') {
                $query->where('user_id', $request->user()->id);
            } else {
                $query->where('user_id', $request->user_id);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('guest_email', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($sq) => $sq->where('name', 'like', "%{$search}%"));
            });
        }

        $orders = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json($orders);
    }

    /**
     * Display the specified order.
     */
    public function show($id): JsonResponse
    {
        $order = Order::with([
            'user', 
            'items.product.media', 
            'transactions', 
            'invoices', 
            'coupons',
            'stockMovements'
        ])->findOrFail($id);
        return response()->json($order);
    }

    /**
     * Update order status.
     */
    public function update(Request $request, $id): JsonResponse
    {
        if (!$this->canManageOrders($request)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $order = Order::findOrFail($id);
        $oldStatus = $order->status;
        
        $validated = $request->validate([
            'status' => 'required|string|in:pending,processing,completed,cancelled,failed,cancellation_requested',
        ]);

        $order->update(['status' => $validated['status']]);

        // If order is processing or completed, mark all pending transactions as success 
        // (especially for manual payments like COD or Bank Transfer confirmed by admin)
        if (in_array($validated['status'], ['processing', 'completed'])) {
            \App\Models\Ecommerce\UserTransaction::where('order_id', $order->id)
                ->where('status', 'pending')
                ->update(['status' => 'success']);

            // Stock-out sync (idempotent by order item)
            $this->orderInventorySyncService->syncSaleForOrder(
                $order,
                $request->user()?->id,
                'order_status_update'
            );

            // Auto-issue invoice if configured
            if ($this->settings->get('ecommerce_invoice_auto_issue', true)) {
                try {
                    $this->invoiceService->issueInvoice($order);
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error('Failed to auto-issue invoice: ' . $e->getMessage());
                }
            }
        } elseif ($validated['status'] === 'cancelled') {
            // Auto-void any valid invoices to maintain accounting integrity
            \App\Models\Ecommerce\OrderInvoice::where('order_id', $order->id)
                ->where('status', 'valid')
                ->update(['status' => 'void']);
        }

        // Trigger Hooks
        \App\Facades\Hook::doAction('order_status_updated', $order, $oldStatus, $validated['status']);
        
        if ($validated['status'] === 'completed' && $oldStatus !== 'completed') {
            \App\Facades\Hook::doAction('order_completed', $order);
        }

        return response()->json($order);
    }

    /**
     * Preview refund details before committing.
     */
    public function previewRefund(Request $request, $id): JsonResponse
    {
        if (!$this->canManageOrders($request)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $order = Order::with(['items.product', 'transactions'])->findOrFail($id);

        $validated = $request->validate([
            'items' => 'nullable|array',
            'items.*.order_item_id' => 'required|integer|exists:order_items,id',
            'items.*.qty' => 'required|integer|min:1',
            'restock' => 'nullable|boolean',
            'gateway_refund' => 'nullable|boolean',
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $preview = $this->orderRefundService->preview($order, $validated);
            \App\Facades\Hook::doAction(
                'order.refund.preview.succeeded',
                $order,
                $preview,
                $validated,
                $request->user()?->id
            );
            return response()->json(['data' => $preview]);
        } catch (\Throwable $e) {
            \App\Facades\Hook::doAction(
                'order.refund.preview.failed',
                $order,
                $validated,
                $e,
                $request->user()?->id
            );
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Commit a full or partial refund for an order.
     */
    public function refund(Request $request, $id): JsonResponse
    {
        if (!$this->canManageOrders($request)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $order = Order::with(['items.product', 'transactions'])->findOrFail($id);

        $validated = $request->validate([
            'items' => 'nullable|array',
            'items.*.order_item_id' => 'required|integer|exists:order_items,id',
            'items.*.qty' => 'required|integer|min:1',
            'restock' => 'nullable|boolean',
            'gateway_refund' => 'nullable|boolean',
            'reason' => 'nullable|string|max:500',
        ]);

        \App\Facades\Hook::doAction('order.refund.processing', $order, $validated);

        try {
            $result = $this->orderRefundService->refund($order, $validated, $request->user()?->id);
            \App\Facades\Hook::doAction(
                'order.refund.succeeded',
                $order,
                $result,
                $validated,
                $request->user()?->id
            );
            return response()->json([
                'message' => 'Refund processed successfully',
                'data' => $result,
            ]);
        } catch (\Throwable $e) {
            \App\Facades\Hook::doAction(
                'order.refund.failed',
                $order,
                $validated,
                $e,
                $request->user()?->id
            );
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * List stock movements for an order.
     */
    public function stockMovements(Request $request, $id): JsonResponse
    {
        if (!$this->canManageOrders($request)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $order = Order::findOrFail($id);
        $movements = $this->inventoryLedgerService->listForOrder($order->id);

        return response()->json(['data' => $movements]);
    }

    protected function canManageOrders(Request $request): bool
    {
        $user = $request->user();

        if (!$user) {
            return false;
        }

        return $user->hasRole(['admin', 'editor']) || $user->can('update order') || $user->can('refund order');
    }
}
