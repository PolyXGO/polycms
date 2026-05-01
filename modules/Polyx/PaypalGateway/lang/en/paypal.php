<?php

return [
    'name' => 'PayPal',
    'description' => 'Pay securely with PayPal or Credit/Debit Card',
    'button' => [
        'pay_with' => 'Pay with PayPal',
    ],
    'messages' => [
        'processing' => 'Processing payment...',
        'success' => 'Payment successful!',
        'failed' => 'Payment failed. Please try again.',
        'cancelled' => 'Payment was cancelled.',
    ],
    'admin' => [
        'settings' => 'PayPal Settings',
        'mode' => 'Mode',
        'sandbox' => 'Sandbox (Testing)',
        'live' => 'Live (Production)',
        'client_id' => 'Client ID',
        'client_secret' => 'Client Secret',
        'webhook_id' => 'Webhook ID',
        'test_connection' => 'Test Connection',
        'connection_success' => 'Connection successful!',
        'connection_failed' => 'Connection failed. Please check your credentials.',
    ],
];
