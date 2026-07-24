<?php

namespace Database\Seeders;

use App\Models\Ecommerce\PaymentGateway;
use Illuminate\Database\Seeder;

class PaypalGatewaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PaymentGateway::updateOrCreate(
            ['code' => 'paypal'],
            [
                'name' => 'PayPal',
                'description' => 'Pay via your PayPal account.',
                'is_active' => false,
                'handler_class' => 'Modules\\Polyx\\PaypalGateway\\PaypalGateway',
                'config' => [
                    'client_id' => '',
                    'client_secret' => '',
                    'mode' => 'sandbox', // 'sandbox' or 'live'
                    'webhook_id' => '',
                    'processing_fee' => 0,
                ],
            ]
        );
    }
}
