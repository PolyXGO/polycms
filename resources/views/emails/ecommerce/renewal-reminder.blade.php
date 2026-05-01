<x-mail::message>
# Subscription Expiring Soon

Hi {{ $user->name }},

Your subscription for **{{ $subscription->product->name }}** is expiring in **{{ $days }} days** (on {{ $subscription->expires_at->format('M d, Y') }}).

To ensure uninterrupted service, please renew your subscription.

<x-mail::button :url="config('app.url') . '/account/subscriptions'">
Manage Subscriptions
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
