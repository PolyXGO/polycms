@php
use App\Services\SettingsService;
$settingsService = app(SettingsService::class);
$brandLogo = $settingsService->get('brand_logo');
$brandName = $settingsService->get('brand_name', 'POLYCMS');
@endphp

@if($brandLogo)
    <img 
        src="{{ $brandLogo }}" 
        alt="{{ $brandName }}" 
        class="{{ $attributes->get('class', '') }}"
        style="{{ $attributes->get('style', '') }}"
    />
@else
    <span class="{{ $attributes->get('class', '') }}" style="{{ $attributes->get('style', '') }}">
        {{ $brandName ?: 'POLYCMS' }}
    </span>
@endif

