<x-mail::message>
# Payment Received

Hi {{ $user->name }},

We've successfully received your payment for order **#{{ $order->code }}**. Your order status has been updated to **{{ $order->status }}**.

@if($order->status === 'completed')
Your digital goods or services are now ready for use.
@endif

<x-mail::button :url="config('app.url') . '/account/orders/' . $order->id">
View Your Order
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
