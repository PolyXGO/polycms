<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmailTemplate;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'key' => 'order_placed',
                'subject' => 'Order Received: #{{ order_number }}',
                'body' => "<h1>Thank you for your order!</h1><p>Hi {{ customer_name }},</p><p>We have received your order #{{ order_number }}. Total: {{ total_amount }} {{ currency }}.</p><p>We will process it as soon as payment is confirmed.</p>",
                'group' => 'ecommerce',
                'variables' => [
                    'order_number' => 'The order identification number',
                    'customer_name' => 'Name of the customer',
                    'total_amount' => 'Total amount of the order',
                    'currency' => 'Currency code',
                ]
            ],
            [
                'key' => 'payment_received',
                'subject' => 'Payment Confirmed for Order #{{ order_number }}',
                'body' => "<h1>Payment Received!</h1><p>Hi {{ customer_name }},</p><p>We've successfully received payment for your order #{{ order_number }}.</p><p>Your order is now being processed.</p>",
                'group' => 'ecommerce',
                'variables' => [
                    'order_number' => 'The order identification number',
                    'customer_name' => 'Name of the customer',
                ]
            ],
            [
                'key' => 'license_key_delivered',
                'subject' => 'Your License Key for {{ product_name }}',
                'body' => "<h1>Your License Key</h1><p>Hi {{ customer_name }},</p><p>Here is your license key for {{ product_name }}:</p><div style='padding: 20px; background: #f4f4f4; text-align: center; font-size: 1.25rem;'><code>{{ license_key }}</code></div><p>You can manage your activations in your account area.</p>",
                'group' => 'ecommerce',
                'variables' => [
                    'product_name' => 'Name of the product',
                    'customer_name' => 'Name of the customer',
                    'license_key' => 'The generated license key',
                ]
            ],
            [
                'key' => 'subscription_renewal_reminder',
                'subject' => 'Action Required: Your subscription expires in {{ days_remaining }} days',
                'body' => "<h1>Subscription Renewal Reminder</h1><p>Hi {{ customer_name }},</p><p>Your subscription for {{ product_name }} will expire in {{ days_remaining }} days (on {{ expiry_date }}).</p><p>Please renew your subscription to maintain access to your services.</p><p><a href='{{ renewal_url }}'>Renew Now</a></p>",
                'group' => 'ecommerce',
                'variables' => [
                    'product_name' => 'Name of the product',
                    'customer_name' => 'Name of the customer',
                    'days_remaining' => 'Number of days left until expiry',
                    'expiry_date' => 'The date when the subscription expires',
                    'renewal_url' => 'URL to the renewal checkout page',
                ]
            ],
        ];

        foreach ($templates as $template) {
            EmailTemplate::updateOrCreate(
                ['key' => $template['key']],
                $template
            );
        }
    }
}
