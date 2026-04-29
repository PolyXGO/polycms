<?php

namespace Modules\Polyx\SepayGateway;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use App\Facades\Hook;
use App\Models\Ecommerce\PaymentGateway;

class SepayGatewayServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(SepayGateway::class, function ($app) {
            return new SepayGateway();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerRoutes();
        $this->registerTranslations();
        $this->seedPaymentGateway();
        $this->registerHooks();
    }

    /**
     * Register routes.
     */
    protected function registerRoutes(): void
    {
        Route::prefix('api')
            ->middleware('api')
            ->group(__DIR__ . '/routes/api.php');
    }

    /**
     * Register translations.
     */
    protected function registerTranslations(): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/lang', 'sepay');
    }

    /**
     * Seed payment gateway configuration.
     */
    protected function seedPaymentGateway(): void
    {
        if (!app()->runningInConsole() && !$this->app->environment('testing')) {
            try {
                PaymentGateway::firstOrCreate(
                    ['code' => 'sepay'],
                    [
                        'name' => 'SePay QR Code',
                        'description' => 'Pay via QR Code with Vietnamese banks (ACB, VCB, BIDV, MB, etc.)',
                        'is_active' => false,
                        'handler_class' => SepayGateway::class,
                        'config' => [
                            'banks' => [],
                            'api_key' => '',
                        ],
                    ]
                );
            } catch (\Exception $e) {
                // Silently fail if database is not ready
            }
        }
    }

    protected function registerHooks(): void
    {
        Hook::addFilter('payment.gateway.config_schema', function ($schema, $gateway = null) {
            if (($gateway->code ?? null) !== 'sepay') {
                return $schema;
            }

            return [
                [
                    'key' => 'banks',
                    'label' => 'Banks',
                    'type' => 'json',
                    'order' => 10,
                    'description' => 'JSON array of bank accounts. Example item: {"bank_code":"VCB","bank_name":"Vietcombank","account_number":"123456789","account_holder":"NGUYEN VAN A","is_primary":true}',
                ],
                [
                    'key' => 'api_key',
                    'label' => 'Api Key',
                    'type' => 'text',
                    'sensitive' => true,
                    'order' => 20,
                ],
                [
                    'key' => 'processing_fee',
                    'label' => 'Processing Fee',
                    'type' => 'number',
                    'order' => 30,
                ],
            ];
        }, 10, 2);
    }
}
