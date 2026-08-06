<?php

declare(strict_types=1);

namespace App\Services\Ecommerce;

use App\Models\Ecommerce\Order;
use App\Models\Ecommerce\OrderItem;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderFulfillmentService
{
    /**
     * Fulfill subscription and licensing for an order.
     */
    public function fulfillOrder(Order $order): void
    {
        // Load items with product relation
        $order->loadMissing('items.product');

        // Filter items that are services or digital products
        $serviceItems = $order->items->filter(function (OrderItem $item) {
            $product = $item->product;
            return $product && in_array($product->type, ['service', 'digital', 'premium_content']);
        });

        if ($serviceItems->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($order, $serviceItems) {
            // Resolve or create user if order doesn't have one (Guest Checkout)
            $user = $order->user;
            if (!$user) {
                $email = $order->guest_email;
                if (!$email) {
                    Log::warning("OrderFulfillmentService: Order #{$order->id} contains services but has no user_id or guest_email.");
                    return;
                }

                // Check if user already exists with this email
                $user = User::where('email', $email)->first();

                if (!$user) {
                    // Auto-register guest account
                    $fullName = $order->billing_address['full_name'] ?? 'Guest Customer';
                    $user = User::create([
                        'name' => $fullName,
                        'email' => $email,
                        'password' => Hash::make(Str::random(16)),
                    ]);

                    if (method_exists($user, 'assignRole')) {
                        try {
                            $user->assignRole('customer');
                        } catch (\Throwable $e) {
                            Log::error("OrderFulfillmentService: Failed to assign customer role to auto-created user #{$user->id}: " . $e->getMessage());
                        }
                    }

                    Log::info("OrderFulfillmentService: Auto-created user account #{$user->id} for guest email: {$email}.");
                }

                // Associate user with the order
                $order->update(['user_id' => $user->id]);
            }

            // Fulfill each service item
            $subscriptionManager = app(SubscriptionManager::class);
            $licenseManager = app(LicenseManager::class);

            foreach ($serviceItems as $item) {
                $metadata = $item->metadata ?? [];
                $product = $item->product;

                // Check if this item is already fulfilled
                if (!empty($metadata['fulfilled'])) {
                    continue;
                }

                try {
                    // Resolve service_id
                    $serviceId = $item->service_id;
                    if (!$serviceId && $product) {
                        $service = \App\Models\Ecommerce\ProductService::where('product_id', $product->id)->first();
                        if (!$service) {
                            $service = \App\Models\Ecommerce\ProductService::create([
                                'product_id' => $product->id,
                                'code' => 'SVC-' . strtoupper(Str::random(8)),
                                'name' => $product->name . ' - Standard License',
                                'access_type' => 'permanent',
                                'price' => $product->price ?? 0,
                                'license_policy' => [
                                    'type' => 'domain',
                                    'max_activations' => 5,
                                ],
                            ]);
                        }
                        $serviceId = $service->id;
                        $item->update(['service_id' => $serviceId]);
                    }

                    if (!$serviceId) {
                        continue;
                    }

                    // Activate subscription and issue independent licenses for each unit in quantity
                    $quantity = max(1, (int) ($item->quantity ?? 1));
                    $createdSubscriptions = [];
                    $createdLicenses = [];

                    for ($q = 0; $q < $quantity; $q++) {
                        $subscription = $subscriptionManager->activateSubscription($user, $serviceId, $product->id);
                        $license = $licenseManager->issueLicense($subscription);

                        $createdSubscriptions[] = $subscription->id;
                        if ($license) {
                            $createdLicenses[] = [
                                'id' => $license->id,
                                'key' => $license->license_key,
                                'unit' => $q + 1,
                            ];
                        }
                    }

                    // Store primary subscription/license for backward compatibility
                    $metadata['subscription_id'] = $createdSubscriptions[0] ?? null;
                    $metadata['subscription_ids'] = $createdSubscriptions;

                    if (!empty($createdLicenses)) {
                        $metadata['license_id'] = $createdLicenses[0]['id'];
                        $metadata['license_key'] = $createdLicenses[0]['key'];
                        $metadata['licenses'] = $createdLicenses;
                    }

                    // Mark as fulfilled
                    $metadata['fulfilled'] = true;
                    $item->update(['metadata' => $metadata]);

                    Log::info("OrderFulfillmentService: Fulfilled order item #{$item->id} (Quantity: {$quantity}, Licenses generated: " . count($createdLicenses) . ") for order #{$order->id}.");
                } catch (\Throwable $e) {
                    Log::error("OrderFulfillmentService: Failed to fulfill order item #{$item->id} for order #{$order->id}: " . $e->getMessage());
                }
            }
        });
    }
}
