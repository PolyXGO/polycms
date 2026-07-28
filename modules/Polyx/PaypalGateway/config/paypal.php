<?php

return [
    'mode' => env('PAYPAL_MODE', 'sandbox'), // sandbox or live
    
    'sandbox' => [
        'client_id' => env('PAYPAL_SANDBOX_CLIENT_ID', ''),
        'client_secret' => env('PAYPAL_SANDBOX_CLIENT_SECRET', ''),
        'app_id' => 'APP-80W284485P519543T',
    ],
    
    'live' => [
        'client_id' => env('PAYPAL_LIVE_CLIENT_ID', ''),
        'client_secret' => env('PAYPAL_LIVE_CLIENT_SECRET', ''),
        'app_id' => '',
    ],
    
    'payment_action' => 'Sale', // Can only be 'Sale', 'Authorization' or 'Order'
    'currency'       => env('PAYPAL_CURRENCY', 'USD'),
    'notify_url'     => env('PAYPAL_NOTIFY_URL', ''), // Change this accordingly for your application.
    'locale'         => env('PAYPAL_LOCALE', 'en_US'), // force gateway language  i.e. it_IT, es_ES, en_US ... (for express checkout only)
    'validate_ssl'   => env('PAYPAL_VALIDATE_SSL', true), // Validate SSL when creating api client.

    // Webhook ID for verification
    'webhook_id' => env('PAYPAL_WEBHOOK_ID', ''),
    
    // UI Settings
    'button' => [
        'color' => 'gold',
        'shape' => 'rect',
        'label' => 'paypal',
        'height' => 45,
    ],

    // Smart Payment Settings
    'target_currency' => 'USD', // Default currency to convert to if unsupported
    'exchange_rates' => [
        'VND_USD' => 0.00004, // Manual rate: 1 VND = 0.00004 USD
        // Add more pairs as needed
    ],
];
