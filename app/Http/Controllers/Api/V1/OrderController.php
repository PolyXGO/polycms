<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Ecommerce\Order;
use App\Models\Ecommerce\OrderNote;
use App\Services\Ecommerce\CouponManager;
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
            'items.service',
            'transactions', 
            'invoices', 
            'coupons',
            'stockMovements',
            'notes.user'
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

            if ($order->payment_status !== 'paid') {
                $order->update(['payment_status' => 'paid']);
            }

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

    // ─── Order Notes CRUD ─────────────────────────────────────

    /**
     * List all notes for an order.
     */
    public function listNotes(Request $request, $orderId): JsonResponse
    {
        if (!$this->canManageOrders($request)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $order = Order::findOrFail($orderId);
        $notes = $order->notes()->with('user')->orderByDesc('created_at')->get();

        return response()->json(['data' => $notes]);
    }

    /**
     * Store a new note for an order.
     */
    public function storeNote(Request $request, $orderId): JsonResponse
    {
        if (!$this->canManageOrders($request)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $order = Order::findOrFail($orderId);

        $validated = $request->validate([
            'content' => 'required|string|max:5000',
            'type' => 'nullable|string|in:' . implode(',', OrderNote::TYPES),
            'is_customer_visible' => 'nullable|boolean',
        ]);

        $note = $order->notes()->create([
            'user_id' => $request->user()->id,
            'type' => $validated['type'] ?? 'note',
            'content' => $validated['content'],
            'is_customer_visible' => $validated['is_customer_visible'] ?? false,
        ]);

        $note->load('user');

        return response()->json([
            'success' => true,
            'message' => 'Note added successfully.',
            'data' => $note,
        ], 201);
    }

    /**
     * Update an existing note for an order.
     */
    public function updateNote(Request $request, $orderId, $noteId): JsonResponse
    {
        if (!$this->canManageOrders($request)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $order = Order::findOrFail($orderId);
        $note = $order->notes()->where('id', $noteId)->firstOrFail();

        $validated = $request->validate([
            'content' => 'sometimes|required|string|max:5000',
            'type' => 'nullable|string|in:' . implode(',', OrderNote::TYPES),
            'is_customer_visible' => 'nullable|boolean',
        ]);

        if (isset($validated['content'])) {
            $note->content = $validated['content'];
        }
        if (isset($validated['type'])) {
            $note->type = $validated['type'];
        }
        if (isset($validated['is_customer_visible'])) {
            $note->is_customer_visible = $validated['is_customer_visible'];
        }

        $note->save();
        $note->load('user');

        return response()->json([
            'success' => true,
            'message' => 'Note updated successfully.',
            'data' => $note,
        ]);
    }

    /**
     * Delete a note from an order.
     */
    public function deleteNote(Request $request, $orderId, $noteId): JsonResponse
    {
        if (!$this->canManageOrders($request)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $order = Order::findOrFail($orderId);
        $note = $order->notes()->where('id', $noteId)->firstOrFail();
        $note->delete();

        return response()->json([
            'success' => true,
            'message' => 'Note deleted successfully.',
        ]);
    }

    // ─── Apply Coupon to Pending Order ───────────────────────

    /**
     * Apply a coupon code to a pending order (admin action).
     * - 100% discount: auto-complete order + fulfill licenses
     * - Partial discount: recalculate totals
     */
    public function applyCoupon(Request $request, $orderId): JsonResponse
    {
        if (!$this->canManageOrders($request)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $order = Order::with(['items.product', 'coupons', 'transactions'])->findOrFail($orderId);

        // Only allow applying coupons to pending orders
        if ($order->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Coupons can only be applied to pending orders.',
            ], 422);
        }

        if ($order->payment_status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'This order has already been paid.',
            ], 422);
        }

        $validated = $request->validate([
            'coupon_code' => 'required|string',
            'confirm' => 'nullable|boolean',
        ]);

        $couponManager = app(CouponManager::class);

        try {
            $result = $couponManager->applyCoupon($order, $validated['coupon_code'], $request->user());
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        $coupon = $result['coupon'];
        $discountAmount = (float) $result['discount_amount'];

        // Calculate existing discounts from already applied coupons
        $existingDiscount = (float) $order->coupons->sum('discount_amount');
        $totalDiscount = $existingDiscount + $discountAmount;
        $newTotal = max(0, (float) $order->subtotal_amount - $totalDiscount);

        $isFullDiscount = $newTotal <= 0;

        // If 100% discount and not yet confirmed, return preview for frontend confirm modal
        if ($isFullDiscount && empty($validated['confirm'])) {
            return response()->json([
                'success' => true,
                'requires_confirm' => true,
                'message' => 'This coupon will cover 100% of the order total. The order will be auto-completed and related licenses will be activated.',
                'data' => [
                    'coupon_code' => $coupon->code,
                    'discount_amount' => $discountAmount,
                    'new_total' => 0,
                    'auto_complete' => true,
                ],
            ]);
        }

        // Apply coupon
        \App\Models\Ecommerce\OrderCoupon::create([
            'order_id' => $order->id,
            'product_coupon_id' => $coupon->id,
            'code' => $coupon->code,
            'discount_amount' => $discountAmount,
        ]);

        // Increment usage count
        $coupon->increment('usage_count');

        // Update order totals
        $order->update([
            'discount_code' => $coupon->code,
            'discount_amount' => $totalDiscount,
            'total_amount' => $newTotal,
        ]);

        // Log note
        $noteContent = $isFullDiscount
            ? "Coupon \"{$coupon->code}\" applied by admin (100% discount). Order auto-completed."
            : "Coupon \"{$coupon->code}\" applied by admin. Discount: " . number_format($discountAmount, 2) . ". Remaining: " . number_format($newTotal, 2) . ".";

        OrderNote::create([
            'order_id' => $order->id,
            'user_id' => $request->user()->id,
            'type' => 'coupon',
            'content' => $noteContent,
            'metadata' => [
                'coupon_code' => $coupon->code,
                'discount_amount' => $discountAmount,
                'new_total' => $newTotal,
            ],
            'is_customer_visible' => true,
        ]);

        $autoCompleted = false;

        if ($isFullDiscount) {
            // Auto-complete: update payment + order status
            \App\Models\Ecommerce\UserTransaction::where('order_id', $order->id)
                ->where('status', 'pending')
                ->update(['status' => 'success']);

            $oldStatus = $order->status;
            $order->update([
                'payment_status' => 'paid',
                'status' => 'completed',
            ]);

            // Trigger hooks for fulfillment (subscription + license issuance)
            \App\Facades\Hook::doAction('order_status_updated', $order, $oldStatus, 'completed');
            \App\Facades\Hook::doAction('order_completed', $order);

            // Stock sync
            $this->orderInventorySyncService->syncSaleForOrder(
                $order,
                $request->user()?->id,
                'coupon_auto_complete'
            );

            // Auto-issue invoice
            if ($this->settings->get('ecommerce_invoice_auto_issue', true)) {
                try {
                    $this->invoiceService->issueInvoice($order);
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error('Failed to auto-issue invoice after coupon: ' . $e->getMessage());
                }
            }

            $autoCompleted = true;
        }

        $order->refresh();
        $order->load(['user', 'items.product.media', 'items.service', 'transactions', 'invoices', 'coupons', 'stockMovements', 'notes.user']);

        return response()->json([
            'success' => true,
            'message' => $isFullDiscount
                ? 'Coupon applied. Order auto-completed with 100% discount.'
                : 'Coupon applied successfully. Remaining amount: ' . number_format($newTotal, 2) . '.',
            'data' => [
                'order' => $order,
                'discount_amount' => $discountAmount,
                'new_total' => $newTotal,
                'auto_completed' => $autoCompleted,
            ],
        ]);
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
