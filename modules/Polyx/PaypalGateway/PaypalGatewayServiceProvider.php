<?php

namespace Modules\Polyx\PaypalGateway;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use App\Facades\Hook;
use App\Models\Ecommerce\PaymentGateway;

class PaypalGatewayServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/paypal.php',
            'paypal'
        );

        $this->app->singleton(Services\PaypalApiClient::class, function ($app) {
            return new Services\PaypalApiClient();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/routes/api.php');
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'paypal');
        $this->loadTranslationsFrom(__DIR__ . '/lang', 'paypal');

        // Register PayPal gateway in the payment system
        $this->registerPaymentGateway();

        // Register hooks
        $this->registerHooks();
    }

    /**
     * Register PayPal as a payment gateway.
     */
    protected function registerPaymentGateway(): void
    {
        // Skip during console commands (migrations, seeders, etc.)
        if (app()->runningInConsole()) {
            return;
        }

        try {
            // Only create if doesn't exist - don't overwrite user's settings
            PaymentGateway::firstOrCreate(
                ['code' => 'paypal'],
                [
                    'name' => 'PayPal',
                    'description' => 'Pay securely with PayPal or Credit/Debit Card',
                    'handler_class' => PaypalGateway::class,
                    'config' => [  // No json_encode! Model cast handles it
                        'mode' => config('paypal.mode', 'sandbox'),
                        'client_id' => '',
                        'client_secret' => '',
                        'webhook_id' => '',
                    ],
                    'is_active' => false,
                    'sort_order' => 1,
                ]
            );
        } catch (\Exception $e) {
            // Table might not exist yet during migrations
        }
    }

    /**
     * Register module hooks.
     */
    protected function registerHooks(): void
    {
        // Add PayPal to available gateways filter
        Hook::addFilter('payment.gateways.available', function ($gateways) {
            $gateways['paypal'] = [
                'name' => 'PayPal',
                'icon' => 'paypal-icon',
                'handler' => PaypalGateway::class,
            ];
            return $gateways;
        });

        // Register admin settings
        Hook::addAction('admin.settings.payment.fields', function ($form) {
            // PayPal settings will be added via payment gateway config
        });

        // Provide schema for unified core gateway editor UI
        Hook::addFilter('payment.gateway.config_schema', function ($schema, $gateway = null) {
            if (($gateway->code ?? null) !== 'paypal') {
                return $schema;
            }

            return [
                [
                    'key' => 'mode',
                    'label' => 'Mode',
                    'type' => 'select',
                    'options' => [
                        ['label' => 'Sandbox', 'value' => 'sandbox'],
                        ['label' => 'Live', 'value' => 'live'],
                    ],
                    'order' => 10,
                ],
                [
                    'key' => 'client_id',
                    'label' => 'Client ID',
                    'type' => 'text',
                    'order' => 20,
                ],
                [
                    'key' => 'client_secret',
                    'label' => 'Client Secret',
                    'type' => 'text',
                    'sensitive' => true,
                    'order' => 30,
                ],
                [
                    'key' => 'webhook_id',
                    'label' => 'Webhook ID',
                    'type' => 'text',
                    'order' => 40,
                ],
                [
                    'key' => 'processing_fee',
                    'label' => 'Processing Fee',
                    'type' => 'number',
                    'order' => 50,
                ],
            ];
        }, 10, 2);
    }
}
