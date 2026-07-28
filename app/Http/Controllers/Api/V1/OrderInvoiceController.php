<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Ecommerce\Order;
use App\Models\Ecommerce\OrderInvoice;
use App\Services\Ecommerce\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class OrderInvoiceController extends Controller
{
    public function __construct(
        protected InvoiceService $invoiceService
    ) {}

    /**
     * Get all invoices globally (Admin only).
     */
    public function getAllInvoices(Request $request): JsonResponse
    {
        if (!$this->canManage($request)) {
            return response()->json(['message' => 'Forbidden: Admin only'], 403);
        }

        $query = OrderInvoice::with(['order.user']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('order', fn($sq) => $sq->where('code', 'like', "%{$search}%"));
            });
        }

        $invoices = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json($invoices);
    }

    /**
     * Get all invoices for a specific order.
     */
    public function index(Request $request, int $orderId): JsonResponse
    {
        $order = Order::findOrFail($orderId);
        
        if (!$this->canManageOrOwn($request, $order)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $invoices = OrderInvoice::where('order_id', $orderId)->latest()->get();
        return response()->json(['data' => $invoices]);
    }

    /**
     * Manually generate a new invoice for an order.
     */
    public function store(Request $request, int $orderId): JsonResponse
    {
        $order = Order::with(['items.product', 'user', 'coupons'])->findOrFail($orderId);

        if (!$this->canManage($request)) {
            return response()->json(['message' => 'Forbidden: Admin only'], 403);
        }

        try {
            $invoice = $this->invoiceService->issueInvoice($order);
            return response()->json([
                'message' => 'Invoice generated successfully',
                'data' => $invoice
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Generate and stream a PDF invoice for the given invoice snapshot.
     */
    public function download(Request $request, int $invoiceId)
    {
        $invoice = OrderInvoice::with('order')->findOrFail($invoiceId);
        $order = $invoice->order;

        if (!$this->canManageOrOwn($request, $order)) {
            return response()->json([
                'message' => 'Unauthorized. Please log in as admin or order owner.',
            ], 403);
        }

        $snapshot = $invoice->billing_snapshot;

        $data = [
            'invoice' => $invoice,
            'snapshot' => $snapshot,
            'brandLogo' => $this->getLogoPath($snapshot['seller']['name'] ?? 'PolyCMS'),
            'siteUrl' => config('app.url'),
        ];

        if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return response()->json([
                'message' => 'PDF generation requires the barryvdh/laravel-dompdf package.',
            ], 501);
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.ecommerce.invoice', $data);
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download("Invoice-{$invoice->invoice_number}.pdf");
    }

    /**
     * Void an invoice.
     */
    public function voidInvoice(Request $request, int $invoiceId): JsonResponse
    {
        $invoice = OrderInvoice::findOrFail($invoiceId);

        if (!$this->canManage($request)) {
            return response()->json(['message' => 'Forbidden: Admin only'], 403);
        }

        $this->invoiceService->voidInvoice($invoice);

        return response()->json([
            'message' => 'Invoice voided successfully',
            'data' => $invoice->fresh()
        ]);
    }

    /**
     * Resolve absolute logo path for PDF.
     */
    protected function getLogoPath(string $fallbackBrandName): string
    {
        $settings = app(\App\Services\SettingsService::class);
        $brandLogo = $settings->get('brand_logo', '');
        
        if ($brandLogo) {
            $possiblePath = public_path($brandLogo);
            if (file_exists($possiblePath)) {
                return $possiblePath;
            } elseif (file_exists(storage_path('app/public/' . $brandLogo))) {
                return storage_path('app/public/' . $brandLogo);
            }
        }
        return '';
    }

    protected function canManage(Request $request): bool
    {
        $user = auth('sanctum')->user() ?? auth('web')->user();
        return $user && method_exists($user, 'hasRole') && $user->hasRole('admin');
    }

    protected function canManageOrOwn(Request $request, Order $order): bool
    {
        $user = auth('sanctum')->user() ?? auth('web')->user();
        $isAdmin = $user && method_exists($user, 'hasRole') && $user->hasRole('admin');
        $isOwner = $user && $order->user_id === $user->id;
        
        return $isAdmin || $isOwner;
    }
}
