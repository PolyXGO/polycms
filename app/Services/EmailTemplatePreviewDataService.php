<?php

namespace App\Services;

use App\Models\EmailTemplate;
use App\Models\Product;
use App\Models\User;
use App\Models\Ecommerce\Order;
use App\Models\Ecommerce\UserSubscription;
use Illuminate\Support\Str;

class EmailTemplatePreviewDataService
{
    public function buildForTemplate(EmailTemplate $template): array
    {
        $user = User::query()->inRandomOrder()->first();
        $order = Order::query()->with('user')->inRandomOrder()->first();
        $subscription = UserSubscription::query()->with(['user', 'product'])->inRandomOrder()->first();
        $product = Product::query()->inRandomOrder()->first();

        $siteName = (string) config('app.name', 'PolyCMS');
        $orderCode = (string) ($order?->code ?? strtoupper(Str::random(8)));
        $currency = (string) ($order?->currency ?? 'USD');
        $totalAmount = (float) ($order?->total_amount ?? 99.00);
        $refundAmount = (float) ($order?->refunded_total ?? round($totalAmount * 0.25, 2));
        $requestCode = 'RR-' . strtoupper(Str::random(6));

        $daysRemaining = 7;
        if ($subscription?->expires_at) {
            $daysRemaining = max(0, (int) now()->diffInDays($subscription->expires_at, false));
        }

        $data = [
            'site_name' => $siteName,
            'site_url' => url('/'),
            'user_name' => (string) ($user?->name ?? $order?->user?->name ?? $subscription?->user?->name ?? 'John Doe'),
            'user_email' => (string) ($user?->email ?? $order?->user?->email ?? 'customer@example.com'),

            'order_code' => $orderCode,
            'total_amount' => number_format($totalAmount, 2),
            'currency' => $currency,
            'payment_status' => (string) ($order?->payment_status ?? 'paid'),

            'product_name' => (string) ($subscription?->product?->name ?? $product?->name ?? 'Sample Product'),
            'days_remaining' => (string) $daysRemaining,
            'renewal_url' => url('/account/subscriptions'),

            'refund_amount' => number_format($refundAmount, 2),
            'refund_status' => (string) ($order?->refund_status ?? 'pending'),
            'reason' => (string) ($order?->refund_reason ?? 'Customer changed mind'),
            'request_code' => $requestCode,
            'request_status' => (string) ($order?->refund_status ?? 'pending'),
            'admin_note' => 'This is a preview note generated from sample data.',

            'account_login_url' => url('/account/login'),
            'account_dashboard_url' => url('/account/dashboard'),
            'account_orders_url' => url('/account/orders'),
            'account_subscriptions_url' => url('/account/subscriptions'),
            'account_licenses_url' => url('/account/licenses'),
            'account_profile_url' => url('/account/profile'),
            'admin_refund_requests_url' => url('/admin/orders'),
            'reset_url' => url('/reset-password/' . Str::random(32)),
        ];

        return $this->filterByTemplateVariables($template, $data);
    }

    protected function filterByTemplateVariables(EmailTemplate $template, array $data): array
    {
        if (empty($template->variables)) {
            return $data;
        }

        $keys = [];
        foreach ((array) $template->variables as $key => $value) {
            if (is_string($key) && $key !== '' && !is_numeric($key)) {
                $keys[] = $key;
                continue;
            }
            if (is_string($value) && $value !== '') {
                $keys[] = $value;
            }
        }

        $keys = array_values(array_unique($keys));
        if (empty($keys)) {
            return $data;
        }

        $filtered = [];
        foreach ($keys as $key) {
            $filtered[$key] = $data[$key] ?? '{' . $key . '}';
        }

        // Keep global URLs/site context available for fallback placeholders.
        $filtered['site_name'] = $data['site_name'];
        $filtered['site_url'] = $data['site_url'];

        return $filtered;
    }
}

