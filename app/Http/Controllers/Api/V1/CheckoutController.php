<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Ecommerce\ProductCoupon;
use App\Services\Ecommerce\OrderManager;
use App\Services\Ecommerce\PaymentManager;
use App\Services\Ecommerce\CouponManager;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    public function __construct(
        protected OrderManager $orderManager,
        protected PaymentManager $paymentManager,
        protected CouponManager $couponManager
    ) {}

    /**
     * Calculate totals for the cart (Preview).
     */
    public function calculate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variant_id' => 'nullable|integer',
            'items.*.service_id' => 'nullable|integer|exists:product_services,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.name' => 'required|string',
            'items.*.image_url' => 'nullable|string',
            'items.*.sku' => 'nullable|string',
            'items.*.variant_label' => 'nullable|string',
            'coupon_code' => 'nullable|string',
            'coupon_codes' => 'nullable|array', // New array input
            'coupon_codes.*' => 'string',
            'billing_address' => 'nullable|array',
        ]);

        $items = $validated['items'];
        
        $productIds = array_column($items, 'product_id');
        $hasTestProducts = \App\Models\Product::whereIn('id', $productIds)
            ->where('slug', 'like', 'test-%')
            ->exists();

        if ($hasTestProducts) {
            $user = auth('sanctum')->user() ?? $request->user();
            $isAdmin = $user && method_exists($user, 'hasRole') && $user->hasRole('admin');
            if (!$isAdmin) {
                return response()->json([
                    'message' => 'Test products can only be ordered by system administrators.',
                    'errors' => [
                        'items' => ['Test products can only be ordered by system administrators.']
                    ]
                ], 403);
            }
        }

        // Validate max_per_order limits & disabled_add_to_cart status
        $productsById = \App\Models\Product::whereIn('id', array_column($items, 'product_id'))->get()->keyBy('id');
        foreach ($items as $item) {
            $p = $productsById->get($item['product_id']);
            if ($p && $p->stock_status === 'disabled_add_to_cart') {
                return response()->json([
                    'message' => "Sales for \"{$p->name}\" are currently paused.",
                    'errors' => [
                        'items' => ["Sales for \"{$p->name}\" are currently paused."]
                    ]
                ], 422);
            }
            if ($p && $p->max_per_order && $p->max_per_order > 0 && $item['quantity'] > $p->max_per_order) {
                return response()->json([
                    'message' => "Quantity for \"{$p->name}\" exceeds the maximum allowed limit of {$p->max_per_order} per order.",
                    'errors' => [
                        'items' => ["Quantity for \"{$p->name}\" exceeds the maximum allowed limit of {$p->max_per_order} per order."]
                    ]
                ], 422);
            }
        }

        $data = $validated;
        
        // Calculate subtotal and apply cart.totals filter for offer calculations
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += ($item['original_price'] ?? $item['price']) * $item['quantity'];
        }

        $rawTotals = [
            'items' => $items,
            'subtotal' => $subtotal,
            'discount' => 0,
            'tax' => 0,
            'total' => 0,
        ];
        $calculatedTotals = \App\Facades\Hook::applyFilters('cart.totals', $rawTotals, null);
        if (!empty($calculatedTotals['items']) && is_array($calculatedTotals['items'])) {
            $items = $calculatedTotals['items'];
        }
        $totalVolumeDiscount = (float) ($calculatedTotals['total_volume_discount'] ?? 0);
        $subtotal = (float) ($calculatedTotals['subtotal'] ?? $subtotal);

        // Normalize Coupons
        $codes = $validated['coupon_codes'] ?? [];
        if (!empty($validated['coupon_code'])) {
            $codes[] = $validated['coupon_code'];
        }
        $codes = array_unique($codes);

        $appliedCoupons = [];
        $totalDiscount = 0;
        $couponDetails = [];
        $firstError = null;

        if (!empty($codes)) {
            // Fetch all potentially valid coupons first
            $coupons = ProductCoupon::whereIn('code', $codes)->where('is_active', true)->get()->keyBy('code');

            foreach ($codes as $code) {
                try {
                    $coupon = $coupons->get($code);
                    if (!$coupon) {
                         // Silent fail or note error? For preview, maybe just ignore or list invalid?
                         // Let's throw to catch block if we want to report strict error, 
                         // but for multi-coupon, one invalid shouldn't break others?
                         // Strategy: Continue but report error?
                         // For now, simple approach: If specific code fails, ignore it but maybe store error.
                        throw new \Exception("Invalid code: $code");
                    }

                    // Validate against Subtotal and EXISTING applied set
                    $this->couponManager->validateCoupon($coupon, $subtotal, $request->user(), $appliedCoupons);
                    
                    // Calculate discount
                    $discount = $this->couponManager->calculateDiscount($coupon, $subtotal); // Note: Should we subtract previous discount from subtotal? Usually coupons apply to original subtotal OR remaining.
                    // Assuming additive for now based on typical "Combine" logic, unless they are percentage of "Remaining".
                    // Most systems (Shopify/Woo) stack them on Subtotal or explicitly sequential.
                    // Given `calculateDiscount` uses `min($value, $subtotal)`, it clamps to subtotal.
                    // We should probably clamp total discount to subtotal at the end.
                    
                    $appliedCoupons[] = $coupon;
                    $totalDiscount += $discount;
                    
                    $couponDetails[] = [
                        'code' => $coupon->code,
                        'discount' => $discount,
                        'title' => $coupon->title,
                        'description' => $coupon->description,
                        'is_exclusive' => $coupon->is_exclusive
                    ];

                } catch (\Exception $e) {
                     if (!$firstError) $firstError = $e->getMessage();
                     // Don't add to appliedCoupons
                }
            }
        }
        
        // Cap discount at subtotal
        if ($totalDiscount > $subtotal) {
            $totalDiscount = $subtotal;
        }

        $data['discount_amount'] = $totalDiscount;
        // Legacy single field: use first or comma list
        $data['discount_code'] = !empty($couponDetails) ? implode(',', array_column($couponDetails, 'code')) : null; 
        
        // Basic Tax Calculation
        $data['tax_amount'] = 0; 

        $totals = [
            'subtotal' => round($subtotal, 2),
            'total_volume_discount' => round($totalVolumeDiscount, 2),
            'discount' => round($totalDiscount, 2),
            'tax' => 0,
            'total' => max(0, round($subtotal - $totalVolumeDiscount - $totalDiscount, 2)),
        ];

        // Fetch product slugs and images to refresh frontend
        $productIds = array_column($items, 'product_id');
        $products = \App\Models\Product::with('media')->whereIn('id', $productIds)->get()->keyBy('id');

        foreach ($items as &$item) {
            if ($product = $products->get($item['product_id'])) {
                $item['slug'] = $product->slug;
                // Use route helper to match frontend behavior exactly
                try {
                    $item['permalink'] = route('products.show', ['slug' => $item['slug']]);
                } catch (\Exception $e) {
                     // Fallback if route not defined or other error
                    $item['permalink'] = theme_permalink_url('products', $item['slug'], 'single');
                }
                // Attach Image URL
                // Check for primary image in pivot (loaded via media relation) or fallback to first image
                $primaryImage = $product->media->first(fn($m) => $m->pivot->is_primary);
                $fallbackImage = $product->media->first();
                
                $item['image_url'] = $primaryImage?->url ?? $fallbackImage?->url;
            }
        }

        return response()->json(array_merge($totals, [
            'items' => $items, // Return refreshed items with offers
            'total_volume_discount' => $totalVolumeDiscount,
            'discount_code' => $data['discount_code'], 
            'applied_coupons' => $couponDetails, 
            'coupon_error' => $firstError
        ]));
    }

    /**
     * Process the checkout and create order.
     */
    /**
     * Process the checkout and create order.
     */
    public function process(Request $request): JsonResponse
    {
        // Try Sanctum token first (if frontend sent Bearer token), then fallback to default web session guard
        $user = auth('sanctum')->user() ?? $request->user();

        $rules = [
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variant_id' => 'nullable|integer',
            'items.*.service_id' => 'nullable|integer|exists:product_services,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.name' => 'required|string',
            'items.*.image_url' => 'nullable|string',
            'items.*.sku' => 'nullable|string',
            'items.*.variant_label' => 'nullable|string',
            'coupon_code' => 'nullable|string',
            'coupon_codes' => 'nullable|array',
            'coupon_codes.*' => 'string',
            'billing_address' => 'required|array',
            'billing_address.full_name' => 'required|string',
            'payment_gateway' => 'nullable|string',
            'payment_data' => 'nullable|array',
        ];

        // Require email if guest
        if (!$user) {
            $rules['customer_email'] = 'required|email';
        }

        $validated = $request->validate($rules);

        $items = $validated['items'];
        
        $productIds = array_column($items, 'product_id');
        $hasTestProducts = \App\Models\Product::whereIn('id', $productIds)
            ->where('slug', 'like', 'test-%')
            ->exists();

        if ($hasTestProducts) {
            $isAdmin = $user && method_exists($user, 'hasRole') && $user->hasRole('admin');
            if (!$isAdmin) {
                return response()->json([
                    'message' => 'Test products can only be ordered by system administrators.',
                    'errors' => [
                        'items' => ['Test products can only be ordered by system administrators.']
                    ]
                ], 403);
            }
        }

        // Validate max_per_order limits & disabled_add_to_cart status
        $productsById = \App\Models\Product::whereIn('id', array_column($items, 'product_id'))->get()->keyBy('id');
        foreach ($items as $item) {
            $p = $productsById->get($item['product_id']);
            if ($p && $p->stock_status === 'disabled_add_to_cart') {
                return response()->json([
                    'message' => "Sales for \"{$p->name}\" are currently paused.",
                    'errors' => [
                        'items' => ["Sales for \"{$p->name}\" are currently paused."]
                    ]
                ], 422);
            }
            if ($p && $p->max_per_order && $p->max_per_order > 0 && $item['quantity'] > $p->max_per_order) {
                return response()->json([
                    'message' => "Quantity for \"{$p->name}\" exceeds the maximum allowed limit of {$p->max_per_order} per order.",
                    'errors' => [
                        'items' => ["Quantity for \"{$p->name}\" exceeds the maximum allowed limit of {$p->max_per_order} per order."]
                    ]
                ], 422);
            }
        }

        $data = $validated;
        $guestEmail = $validated['customer_email'] ?? null;
        
        // Add guest_email to data for Order creation
        if ($guestEmail) {
            $data['guest_email'] = $guestEmail;
        }

        // Recalculate Logic & apply cart.totals filter for offer calculations
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += ($item['original_price'] ?? $item['price']) * $item['quantity'];
        }

        $rawTotals = [
            'items' => $items,
            'subtotal' => $subtotal,
            'discount' => 0,
            'tax' => 0,
            'total' => 0,
        ];
        $calculatedTotals = \App\Facades\Hook::applyFilters('cart.totals', $rawTotals, null);
        if (!empty($calculatedTotals['items']) && is_array($calculatedTotals['items'])) {
            $items = $calculatedTotals['items'];
        }
        $totalVolumeDiscount = (float) ($calculatedTotals['total_volume_discount'] ?? 0);
        $subtotal = (float) ($calculatedTotals['subtotal'] ?? $subtotal);

        $codes = $validated['coupon_codes'] ?? [];
        if (!empty($validated['coupon_code'])) {
            $codes[] = $validated['coupon_code'];
        }
        $codes = array_unique($codes);

        $appliedCoupons = [];
        $totalDiscount = 0;
        $couponDetails = [];

        if (!empty($codes)) {
            $coupons = ProductCoupon::whereIn('code', $codes)->where('is_active', true)->get()->keyBy('code');
            foreach ($codes as $code) {
                try {
                    $coupon = $coupons->get($code);
                    if (!$coupon) throw new \Exception("Invalid code: $code");
                    
                    $this->couponManager->validateCoupon($coupon, $subtotal, $user, $appliedCoupons, $guestEmail);
                    $discount = $this->couponManager->calculateDiscount($coupon, $subtotal);
                    
                    $appliedCoupons[] = $coupon;
                    $totalDiscount += $discount;
                    
                    $couponDetails[] = [
                        'code' => $coupon->code,
                        'discount' => $discount,
                        'id' => $coupon->id
                    ];
                } catch (\Exception $e) {
                     return response()->json(['message' => 'Invalid coupon: ' . $e->getMessage()], 422);
                }
            }
        }

        $netSubtotal = max(0, $subtotal - $totalVolumeDiscount);
        if ($totalDiscount > $netSubtotal) $totalDiscount = $netSubtotal;
        
        $finalTotal = max(0, round($subtotal - $totalVolumeDiscount - $totalDiscount, 2));
        $isFreeOrder = ($finalTotal <= 0);

        $data['discount_amount'] = $totalDiscount;
        $data['discount_code'] = !empty($couponDetails) ? implode(',', array_column($couponDetails, 'code')) : null;

        if ($isFreeOrder) {
            $data['payment_gateway'] = 'free';
        } else {
            if (empty($validated['payment_gateway'])) {
                return response()->json(['message' => 'Please select a payment method.'], 422);
            }
            $gatewayExists = \App\Models\Ecommerce\PaymentGateway::where('code', $validated['payment_gateway'])
                ->where('is_active', true)
                ->exists();
            if (!$gatewayExists) {
                return response()->json(['message' => 'Selected payment gateway is inactive or invalid.'], 422);
            }
        }

        try {
            if ($isFreeOrder) {
                // Free 100% Order -> Process immediately and mark completed without payment gateway
                $order = $this->orderManager->createOrder($user, $items, $data, false);

                foreach ($couponDetails as $detail) {
                    $order->coupons()->create([
                        'product_coupon_id' => $detail['id'],
                        'code' => $detail['code'],
                        'discount_amount' => $detail['discount']
                    ]);
                }

                $oldStatus = $order->status;
                $order->update([
                    'payment_status' => 'paid',
                    'payment_method' => 'free',
                    'status' => 'completed',
                ]);

                \App\Facades\Hook::doAction('order_status_updated', $order, $oldStatus, 'completed');

                // Fulfill order immediately (activates subscription, issues license key, sends email)
                app(\App\Services\Ecommerce\OrderFulfillmentService::class)->fulfillOrder($order);

                $order->load(['items.product:id,name,slug', 'coupons']);

                return response()->json([
                    'order' => $order,
                    'payment_result' => [
                        'status' => 'completed',
                        'is_free' => true,
                        'message' => 'Free order processed and completed successfully',
                        'redirect_url' => route('checkout.success', ['code' => $order->code]),
                    ],
                    'message' => 'Order completed successfully',
                ], 201);
            }

            // Normal Paid Order Creation
            $suppressEmail = ($validated['payment_gateway'] === 'sepay');
            
            $order = $this->orderManager->createOrder($user, $items, $data, $suppressEmail);

            // Save Order Coupons
            foreach ($couponDetails as $detail) {
                $order->coupons()->create([
                    'product_coupon_id' => $detail['id'],
                    'code' => $detail['code'],
                    'discount_amount' => $detail['discount']
                ]);
            }
            
            // Eager load relationships for the response
            $order->load(['items.product:id,name,slug', 'coupons']);
            
            // Process Payment
            $paymentResponse = $this->paymentManager->processPayment(
                $order, 
                $validated['payment_gateway'], 
                $request
            );

            return response()->json([
                'order' => $order,
                'payment_result' => $paymentResponse,
                'message' => 'Order created successfully',
            ], 201);

        } catch (\Exception $e) {
            Log::error('Checkout Failed: ' . $e->getMessage());
            return response()->json(['message' => 'Checkout failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get available coupons (Public/Active or personalized for authenticated customer / admin).
     */
    public function getAvailableCoupons(Request $request): JsonResponse
    {
        $user = $request->user('sanctum') ?? $request->user('web') ?? $request->user();
        
        $isAdmin = false;
        if ($user) {
            if (method_exists($user, 'hasRole') && $user->hasRole('admin')) {
                $isAdmin = true;
            } elseif (in_array($user->role ?? '', ['admin', 'superadmin'], true) || in_array($user->email ?? '', ['polyxgo@gmail.com'], true)) {
                $isAdmin = true;
            }
        }

        $now = \Carbon\Carbon::now();
        $productId = $request->query('product_id');
        $product = $productId ? \App\Models\Ecommerce\Product::find($productId) : null;
        $productCategoryIds = ($product && $product->categories) ? $product->categories->pluck('id')->toArray() : [];

        $query = ProductCoupon::where('is_active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', $now);
            })
            ->where(function ($q) {
                $q->whereNull('usage_limit')->orWhereRaw('usage_count < usage_limit');
            });

        if ($isAdmin) {
            // Admin can see all active coupons for inspection / testing
        } elseif ($user) {
            // Authenticated customer can see public coupons (with no email restrictions) or coupons explicitly assigned to their email
            $query->where(function ($q) use ($user) {
                $q->where(function ($sub) {
                    $sub->where('is_public', true)
                        ->where(function ($s2) {
                            $s2->whereNull('restricted_emails')
                               ->orWhere('restricted_emails', '[]')
                               ->orWhere('restricted_emails', '');
                        });
                })->orWhereJsonContains('restricted_emails', $user->email);
            });
        } else {
            // Public / guest users MUST ONLY see strictly public coupons with NO email restrictions
            $query->where('is_public', true)
                  ->where(function ($q) {
                      $q->whereNull('restricted_emails')
                        ->orWhere('restricted_emails', '[]')
                        ->orWhere('restricted_emails', '');
                  });
        }

        $coupons = $query->get()->filter(function ($coupon) use ($product, $productCategoryIds, $user, $isAdmin) {
            if (!$isAdmin) {
                // Secondary safeguard: if restricted_emails is present and not matching user email, reject
                if (!empty($coupon->restricted_emails) && is_array($coupon->restricted_emails) && count($coupon->restricted_emails) > 0) {
                    if (!$user || !in_array($user->email, $coupon->restricted_emails, true)) {
                        return false;
                    }
                }
                if (!$user && !$coupon->is_public) {
                    return false;
                }
            }

            if (!$product) {
                return true;
            }

            $scope = $coupon->scope_config ?? [];
            if (!empty($scope['excluded_product_ids']) && is_array($scope['excluded_product_ids'])) {
                if (in_array((int)$product->id, array_map('intval', $scope['excluded_product_ids']), true)) {
                    return false;
                }
            }

            $allowedProductIds = !empty($scope['product_ids']) && is_array($scope['product_ids']) ? array_map('intval', $scope['product_ids']) : [];
            $allowedCategoryIds = !empty($scope['category_ids']) && is_array($scope['category_ids']) ? array_map('intval', $scope['category_ids']) : [];

            if (empty($allowedProductIds) && empty($allowedCategoryIds)) {
                return true;
            }

            if (!empty($allowedProductIds) && in_array((int)$product->id, $allowedProductIds, true)) {
                return true;
            }

            if (!empty($allowedCategoryIds) && count(array_intersect($productCategoryIds, $allowedCategoryIds)) > 0) {
                return true;
            }

            return false;
        })->values();

        return response()->json([
            'data' => $coupons->map(function ($coupon) use ($product) {
                $isSpecific = $product && !empty($coupon->scope_config['product_ids']) && in_array((int)$product->id, array_map('intval', $coupon->scope_config['product_ids']), true);
                return [
                    'id' => $coupon->id,
                    'code' => $coupon->code,
                    'title' => $coupon->title ?: $coupon->code,
                    'description' => $coupon->description,
                    'value' => (float) $coupon->value,
                    'type' => $coupon->type,
                    'min_order_value' => (float) ($coupon->min_order_value ?? 0),
                    'is_exclusive' => (bool) $coupon->is_exclusive,
                    'is_public' => (bool) $coupon->is_public,
                    'expires_at' => $coupon->expires_at?->toIso8601String(),
                    'expires_at_formatted' => $coupon->expires_at ? $coupon->expires_at->format('M d, Y') : null,
                    'is_specific' => $isSpecific,
                ];
            })
        ]);
    }
}
