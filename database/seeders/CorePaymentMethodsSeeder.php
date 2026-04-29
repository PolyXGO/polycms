<?php

namespace Database\Seeders;

use App\Models\Ecommerce\PaymentGateway;
use Illuminate\Database\Seeder;

class CorePaymentMethodsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Cash on Delivery (COD)
        PaymentGateway::firstOrCreate(
            ['code' => 'cod'],
            [
                'name' => 'Cash on Delivery (COD)',
                'description' => 'Pay with cash when your order is delivered',
                'is_active' => false,
                'handler_class' => 'core:cod', // Core method identifier
                'sort_order' => 10,
                'config' => [
                    'instructions' => 'Please have exact amount ready for the delivery person.',
                    'min_order_amount' => 0,
                    'max_order_amount' => 0, // 0 = no limit
                    'additional_fee' => 0,
                    'fee_type' => 'fixed', // 'fixed' or 'percentage'
                    'available_areas' => '', // Comma-separated list of areas, empty = all
                ],
            ]
        );

        // Bank Transfer
        PaymentGateway::firstOrCreate(
            ['code' => 'bank_transfer'],
            [
                'name' => 'Bank Transfer',
                'description' => 'Pay by transferring money directly to our bank account',
                'is_active' => false,
                'handler_class' => 'core:bank_transfer', // Core method identifier
                'sort_order' => 11,
                'config' => [
                    'instructions' => 'Please transfer the total amount to one of our bank accounts listed below. Include your order number in the transfer description.',
                    'banks' => [],
                ],
            ]
        );
    }
}
