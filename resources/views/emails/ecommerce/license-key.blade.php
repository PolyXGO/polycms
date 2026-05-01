<x-mail::message>
# Your License Key

Hi {{ $user->name }},

Thank you for your purchase! Here is your license key for **{{ $license->product->name }}**:

<x-mail::panel>
**{{ $license->license_key }}**
</x-panel>

You can manage this license and its activations in your account dashboard.

<x-mail::button :url="config('app.url') . '/account/licenses'">
Manage Licenses
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
