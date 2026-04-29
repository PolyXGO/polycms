<?php

namespace Database\Seeders;

use App\Models\Ecommerce\PaymentGateway;
use Illuminate\Database\Seeder;

class SepayGatewaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PaymentGateway::updateOrCreate(
            ['code' => 'sepay'],
            [
                'name' => 'SePay QR Code',
                'description' => 'Pay via QR Code with Vietnamese banks (ACB, VCB, BIDV, MB, etc.)',
                'is_active' => false,
                'handler_class' => 'Modules\\Polyx\\SepayGateway\\SepayGateway',
                'config' => [
                    'banks' => [],
                    'api_key' => '',
                ],
            ]
        );
    }
}
