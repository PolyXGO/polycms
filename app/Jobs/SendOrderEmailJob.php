<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\Ecommerce\EmailManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Queue job for sending order-related emails.
 *
 * PERFORMANCE: Moves email delivery off the request lifecycle
 *              so checkout never blocks on SMTP latency or errors.
 * SECURITY: Uses SerializesModels — only model IDs are stored in queue payload.
 */
class SendOrderEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Retry up to 3 times with 30-second backoff
     */
    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(
        protected int $orderId,
        protected string $emailType = 'order_placed',
        protected array $extraData = []
    ) {}

    public function handle(EmailManager $emailManager): void
    {
        $order = \App\Models\Ecommerce\Order::with(['items.product', 'user'])->find($this->orderId);

        if (!$order) {
            Log::warning("SendOrderEmailJob: Order #{$this->orderId} not found, skipping.");
            return;
        }

        try {
            switch ($this->emailType) {
                case 'order_placed':
                    $emailManager->sendOrderConfirmation($order);
                    break;

                case 'order_status_changed':
                    $emailManager->sendStatusUpdate($order, $this->extraData['new_status'] ?? $order->status);
                    break;

                default:
                    Log::warning("SendOrderEmailJob: Unknown email type '{$this->emailType}' for order #{$this->orderId}");
            }
        } catch (\Throwable $e) {
            Log::error("SendOrderEmailJob failed for order #{$this->orderId}: " . $e->getMessage());
            throw $e; // Let queue handle retry
        }
    }
}
