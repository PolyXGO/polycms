<?php

namespace Modules\Polyx\SepayGateway;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Ecommerce\Order;
use App\Models\Ecommerce\PaymentGateway;
use Modules\Polyx\SepayGateway\Services\SepayApiClient;
use Illuminate\Http\Request;

class SepayGateway implements PaymentGatewayInterface
{
    protected SepayApiClient $client;
    protected array $config;

    public function __construct()
    {
        $gateway = PaymentGateway::find('sepay');
        $this->config = $gateway?->config ?? [];
        $this->client = new SepayApiClient($this->config);
    }

    /**
     * Get the gateway code/identifier.
     */
    public function getCode(): string
    {
        return 'sepay';
    }

    /**
     * Get the gateway display name.
     */
    public function getName(): string
    {
        return 'SePay QR Code';
    }

    /**
     * Check if the gateway is properly configured.
     */
    public function isConfigured(): bool
    {
        $banks = $this->config['banks'] ?? [];
        return !empty($banks) && count($banks) > 0;
    }

    /**
     * Create a payment for an order.
     * Returns QR code URL for display.
     */
    public function createPayment(Order $order): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'SePay gateway is not configured. Please add bank account details.',
            ];
        }

        // Get primary bank
        $primaryBank = $this->getPrimaryBank();
        if (!$primaryBank) {
            return [
                'success' => false,
                'message' => 'No bank account configured for SePay.',
            ];
        }

        // Generate description from order number (remove dashes for QR content)
        $description = str_replace('-', '', $order->code);

        // Amount in VND (SePay only supports VND)
        $amount = (float) $order->total_amount;

        // Generate QR URL
        $qrUrl = $this->client->generateQrUrl(
            $primaryBank['bank_code'],
            $primaryBank['account_number'],
            $amount,
            $description
        );

        return [
            'success' => true,
            'payment_method' => 'sepay',
            'qr_url' => $qrUrl,
            'amount' => $amount,
            'currency' => 'VND',
            'description' => $description,
            'bank_code' => $primaryBank['bank_code'],
            'bank_name' => $primaryBank['bank_name'] ?? $primaryBank['bank_code'],
            'account_number' => $primaryBank['account_number'],
            'account_holder' => $primaryBank['account_holder'] ?? '',
            'redirect_url' => null, // QR payments don't redirect
        ];
    }

    /**
     * Process webhook callback from SePay.
     */
    public function handleWebhook(Request $request): array
    {
        $payload = $request->all();

        // Validate required fields
        if (!isset($payload['id']) || !isset($payload['transferAmount'])) {
            return [
                'success' => false,
                'message' => 'Invalid webhook payload',
            ];
        }

        // Only process incoming transactions
        $transferType = $payload['transferType'] ?? '';
        if ($transferType !== 'in') {
            return [
                'success' => true,
                'message' => 'Ignoring non-incoming transaction',
            ];
        }

        // Extract order identifier from 'code' or 'content'
        $orderCode = $payload['code'] ?? '';
        $content = $payload['content'] ?? '';
        $amount = (float) ($payload['transferAmount'] ?? 0);
        $sepayTransactionId = $payload['id'] ?? null;

        // Try to match order
        $order = $this->matchOrder($orderCode, $content, $amount);

        if (!$order) {
            return [
                'success' => false,
                'message' => 'Order not found',
            ];
        }

        // Check if already paid
        if ($order->payment_status === 'paid') {
            return [
                'success' => true,
                'message' => 'Order already paid',
            ];
        }

        // Update order status
        $order->update([
            'payment_status' => 'paid',
            'status' => 'completed',
            'transaction_id' => 'SEPAY-' . $sepayTransactionId,
            'paid_at' => now(),
            'gateway_response' => json_encode([
                'sepay_transaction_id' => $sepayTransactionId,
                'gateway' => $payload['gateway'] ?? '',
                'transaction_date' => $payload['transactionDate'] ?? '',
                'account_number' => $payload['accountNumber'] ?? '',
                'transfer_amount' => $amount,
                'reference_code' => $payload['referenceCode'] ?? '',
                'content' => $content,
            ]),
        ]);

        // Send Payment Received Email
        try {
            app(\App\Services\Ecommerce\EmailManager::class)->sendPaymentReceivedEmail($order);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to send SePay payment email: " . $e->getMessage());
        }

        return [
            'success' => true,
            'message' => 'Payment processed successfully',
            'order_id' => $order->id,
        ];
    }

    /**
     * Verify webhook authenticity via API key.
     */
    public function verifyWebhook(Request $request): bool
    {
        $apiKey = $this->config['api_key'] ?? '';
        
        // If no API key configured, allow all (not recommended for production)
        if (empty($apiKey)) {
            return true;
        }

        // Check Authorization header: "Apikey YOUR_API_KEY"
        $authHeader = $request->header('Authorization', '');
        
        if (preg_match('/^Apikey\s+(.+)$/i', $authHeader, $matches)) {
            return hash_equals($apiKey, $matches[1]);
        }

        return false;
    }

    /**
     * Get the primary bank account.
     */
    protected function getPrimaryBank(): ?array
    {
        $banks = $this->config['banks'] ?? [];
        
        if (empty($banks)) {
            return null;
        }

        // Find primary bank
        foreach ($banks as $bank) {
            if (!empty($bank['is_primary'])) {
                return $bank;
            }
        }

        // Return first bank if no primary set
        return $banks[0] ?? null;
    }

    /**
     * Match order by code or content.
     */
    protected function matchOrder(string $code, string $content, float $amount): ?Order
    {
        // Clean code (remove dashes)
        $cleanCode = strtoupper(str_replace('-', '', trim($code)));

        // Try to find by order number
        $orders = Order::where('payment_method', 'sepay')
            ->whereIn('payment_status', ['pending', 'awaiting_payment'])
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        foreach ($orders as $order) {
            $orderNumberClean = str_replace('-', '', strtoupper($order->order_number));

            // Match by code
            if (!empty($cleanCode) && $orderNumberClean === $cleanCode) {
                // Verify amount (1% tolerance)
                $orderTotal = (float) $order->total_amount;
                $tolerance = $orderTotal * 0.01;
                if (abs($amount - $orderTotal) <= $tolerance) {
                    return $order;
                }
            }

            // Match by content
            if (!empty($content) && stripos($content, $orderNumberClean) !== false) {
                $orderTotal = (float) $order->total_amount;
                $tolerance = $orderTotal * 0.01;
                if (abs($amount - $orderTotal) <= $tolerance) {
                    return $order;
                }
            }
        }

        return null;
    }

    /**
     * Get payment status (for polling).
     */
    public function getPaymentStatus(Order $order): array
    {
        return [
            'success' => true,
            'payment_status' => $order->payment_status,
            'is_paid' => $order->payment_status === 'paid',
        ];
    }
}
