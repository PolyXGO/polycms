<x-mail::message>
# Order Confirmation

Hi {{ $user->name }},

Thank you for your order! We've received your order **#{{ $order->code }}** and it is currently being processed.

<x-mail::table>
| Item | Qty | Price |
| :--- | :-- | :---- |
@foreach($order->items as $item)
| {{ $item->name }} | {{ $item->quantity }} | {{ number_format($item->total, 2) }} |
@endforeach
| **Total** | | **{{ number_format($order->total_amount, 2) }} {{ $order->currency }}** |
</x-mail::table>

<x-mail::button :url="config('app.url') . '/account/orders/' . $order->id">
View Order Details
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
