<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Ecommerce\Order;
use App\Models\Ecommerce\OrderItem;
use App\Models\Ecommerce\ProductService;
use App\Services\Ecommerce\SubscriptionManager;
use App\Services\Ecommerce\LicenseManager;

class BackfillMissingLicensesCommand extends Command
{
    protected $signature = 'ecommerce:backfill-missing-licenses {--order= : Specific order code to fix}';
    protected $description = 'Backfill missing subscriptions and license keys for order items purchased with quantity > 1';

    public function handle(SubscriptionManager $subscriptionManager, LicenseManager $licenseManager): int
    {
        $this->info('Scanning completed orders for missing multi-quantity licenses...');

        $orderCode = $this->option('order');
        $query = Order::query()->with('items.product');

        if ($orderCode) {
            $query->where('code', $orderCode);
        } else {
            $query->where(function ($q) {
                $q->whereIn('status', ['completed', 'processing', 'paid'])
                  ->orWhereIn('payment_status', ['paid', 'completed']);
            });
        }

        $orders = $query->get();
        $fixedCount = 0;

        foreach ($orders as $order) {
            $user = $order->user;
            if (!$user) {
                continue;
            }

            foreach ($order->items as $item) {
                $quantity = max(1, (int) ($item->quantity ?? 1));
                if ($quantity <= 1) {
                    continue;
                }

                $product = $item->product;
                if (!$product || !in_array($product->type, ['service', 'digital', 'premium_content', 'license', 'software'])) {
                    continue;
                }

                $metadata = $item->metadata ?? [];
                $existingLicenses = $metadata['licenses'] ?? [];

                // Check legacy single license key
                if (empty($existingLicenses) && !empty($metadata['license_id']) && !empty($metadata['license_key'])) {
                    $existingLicenses[] = [
                        'id' => $metadata['license_id'],
                        'key' => $metadata['license_key'],
                        'unit' => 1,
                    ];
                }

                $existingSubs = $metadata['subscription_ids'] ?? [];
                if (empty($existingSubs) && !empty($metadata['subscription_id'])) {
                    $existingSubs[] = $metadata['subscription_id'];
                }

                $currentLicCount = count($existingLicenses);
                if ($currentLicCount >= $quantity) {
                    continue;
                }

                $missingCount = $quantity - $currentLicCount;
                $this->line("Order #{$order->code} Item #{$item->id} ({$product->name}): Quantity {$quantity}, found {$currentLicCount} license(s). Generating {$missingCount} missing license(s)...");

                // Resolve service_id
                $serviceId = $item->service_id;
                if (!$serviceId) {
                    $service = ProductService::where('product_id', $product->id)->first();
                    if (!$service) {
                        $service = ProductService::create([
                            'product_id' => $product->id,
                            'code' => 'SVC-' . strtoupper(\Illuminate\Support\Str::random(8)),
                            'name' => $product->name . ' - Standard License',
                            'access_type' => 'permanent',
                            'price' => $product->price ?? 0,
                            'license_policy' => ['type' => 'domain', 'max_activations' => 5],
                        ]);
                    }
                    $serviceId = $service->id;
                    $item->update(['service_id' => $serviceId]);
                }

                for ($q = $currentLicCount; $q < $quantity; $q++) {
                    $subscription = $subscriptionManager->activateSubscription($user, $serviceId, $product->id);
                    $license = $licenseManager->issueLicense($subscription);

                    $existingSubs[] = $subscription->id;
                    if ($license) {
                        $existingLicenses[] = [
                            'id' => $license->id,
                            'key' => $license->license_key,
                            'unit' => $q + 1,
                        ];
                    }
                }

                $metadata['subscription_id'] = $existingSubs[0] ?? null;
                $metadata['subscription_ids'] = $existingSubs;
                if (!empty($existingLicenses)) {
                    $metadata['license_id'] = $existingLicenses[0]['id'];
                    $metadata['license_key'] = $existingLicenses[0]['key'];
                    $metadata['licenses'] = $existingLicenses;
                }
                $metadata['fulfilled'] = true;

                $item->update(['metadata' => $metadata]);
                $fixedCount++;
                $this->info("✓ Order #{$order->code} Item #{$item->id}: Updated to {$quantity} independent licenses.");
            }
        }

        $this->info("Completed. Backfilled missing licenses for {$fixedCount} order item(s).");
        return 0;
    }
}
