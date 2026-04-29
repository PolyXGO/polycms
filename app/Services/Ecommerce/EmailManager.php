<?php

namespace App\Services\Ecommerce;

use App\Models\Ecommerce\Order;
use App\Models\Ecommerce\Subscription;
use App\Models\Ecommerce\License;
use App\Mail\Ecommerce\OrderPlacedMail;
use App\Mail\Ecommerce\PaymentReceivedMail;
use App\Mail\Ecommerce\SubscriptionRenewalReminderMail;
use App\Mail\Ecommerce\LicenseKeyMail;
use Illuminate\Support\Facades\Mail;
use App\Services\HookManager;
use App\Services\EmailTemplateManager;
use App\Mail\DynamicTemplateMail;

class EmailManager
{
    public function __construct(
        protected HookManager $hookManager,
        protected EmailTemplateManager $templateManager
    ) {}

    /**
     * Send order placed email to customer.
     */
    public function sendOrderPlacedEmail(Order $order)
    {
        $userName = $order->user ? $order->user->name : ($order->billing_address['full_name'] ?? 'Guest');
        $userEmail = $order->user ? $order->user->email : $order->guest_email;

        $data = [
            'user_name' => $userName,
            'order_code' => $order->code,
            'total_amount' => $order->total_amount . ' ' . $order->currency,
            'site_name' => config('app.name'),
        ];
        $this->sendMail('order_confirmation', new OrderPlacedMail($order), $userEmail, $data);
    }

    /**
     * Send payment received email to customer.
     */
    public function sendPaymentReceivedEmail(Order $order)
    {
        $userName = $order->user ? $order->user->name : ($order->billing_address['full_name'] ?? 'Guest');
        $userEmail = $order->user ? $order->user->email : $order->guest_email;

        $data = [
            'user_name' => $userName,
            'order_code' => $order->code,
            'site_name' => config('app.name'),
        ];
        $this->sendMail('order_success', new PaymentReceivedMail($order), $userEmail, $data);
    }

    /**
     * Send subscription renewal reminder.
     */
    public function sendRenewalReminderEmail(Subscription $subscription, int $daysRemaining)
    {
        $data = [
            'user_name' => $subscription->user->name,
            'product_name' => $subscription->product->name ?? 'Service',
            'days_remaining' => $daysRemaining,
            'renewal_url' => url('/my-account/subscriptions'), // Replace with actual renewal URL if different
            'site_name' => config('app.name'),
        ];
        $this->sendMail('subscription_renewal_reminder', new SubscriptionRenewalReminderMail($subscription, $daysRemaining), $subscription->user->email, $data);
    }

    /**
     * Send license key email.
     */
    public function sendLicenseKeyEmail(License $license)
    {
        $data = [
            'user_name' => $license->user->name,
            'order_code' => $license->order->code ?? 'N/A',
            'license_key' => $license->license_key,
            'product_name' => $license->product->name ?? 'Product',
            'site_name' => config('app.name'),
        ];
        $this->sendMail('license_key_delivered', new LicenseKeyMail($license), $license->user->email, $data);
    }

    /**
     * Send order cancellation request email.
     */
    public function sendOrderCancellationRequestEmail(Order $order)
    {
        $userName = $order->user ? $order->user->name : ($order->billing_address['full_name'] ?? 'Guest');
        $userEmail = $order->user ? $order->user->email : $order->guest_email;

        $data = [
            'user_name' => $userName,
            'order_code' => $order->code,
            'site_name' => config('app.name'),
        ];
        $this->sendMail('order_cancellation_request', new DynamicTemplateMail('ORDER_CANCELLATION_REQUEST', $data), $userEmail, $data);
    }

    /**
     * Alias for sendOrderPlacedEmail — used by SendOrderEmailJob.
     */
    public function sendOrderConfirmation(Order $order): void
    {
        $this->sendOrderPlacedEmail($order);
    }

    /**
     * Send order status update email to customer.
     */
    public function sendStatusUpdate(Order $order, string $newStatus): void
    {
        $userName = $order->user ? $order->user->name : ($order->billing_address['full_name'] ?? 'Guest');
        $userEmail = $order->user ? $order->user->email : $order->guest_email;

        $data = [
            'user_name' => $userName,
            'order_code' => $order->code,
            'new_status' => ucfirst($newStatus),
            'site_name' => config('app.name'),
        ];

        $templateKey = 'ORDER_STATUS_' . strtoupper($newStatus);
        $mailable = new DynamicTemplateMail($templateKey, $data);

        $this->sendMail('order_status_' . $newStatus, $mailable, $userEmail, $data);
    }

    /**
     * Send order cancelled email.
     */
    public function sendOrderCancelledEmail(Order $order)
    {
        $userName = $order->user ? $order->user->name : ($order->billing_address['full_name'] ?? 'Guest');
        $userEmail = $order->user ? $order->user->email : $order->guest_email;

        $data = [
            'user_name' => $userName,
            'order_code' => $order->code,
            'site_name' => config('app.name'),
        ];
        $this->sendMail('order_cancelled', new DynamicTemplateMail('ORDER_CANCELLED', $data), $userEmail, $data);
    }

    /**
     * Helper to send mail with hooks.
     */
    protected function sendMail(string $type, $mailable, $to, array $data = [])
    {
        if (!$this->shouldSendEmail($type, $mailable)) {
            return;
        }

        // Check if database template exists for this type
        $templateKey = strtoupper($type);
        if ($this->templateManager->getTemplate($templateKey)) {
            $mailable = new DynamicTemplateMail($templateKey, $data);
        }

        // Allow modules to override the mailable instance for custom templates/branding
        $mailable = $this->hookManager->applyFilters("ecommerce.emails.mailable.{$type}", $mailable, $data);

        try {
            Mail::to($to)->send($mailable);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Failed to send {$type} email to {$to}: " . $e->getMessage());
        }
    }

    /**
     * Check if email should be sent via hooks.
     */
    protected function shouldSendEmail(string $type, $context): bool
    {
        // Allow modules to disable specific emails
        return $this->hookManager->applyFilters("ecommerce.emails.should_send.{$type}", true, $context);
    }
}
