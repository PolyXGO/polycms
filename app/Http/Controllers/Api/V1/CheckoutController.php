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
        $data = $validated;
        
        // Calculate subtotal
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

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

        $totals = $this->orderManager->calculateTotals($items, $data);

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
            'items' => $items, // Return refreshed items
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
        $user = $request->user();

        $rules = [
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variant_id' => 'nullable|integer',
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
            'payment_gateway' => 'required|string|exists:payment_gateways,code',
            'payment_data' => 'nullable|array',
        ];

        // Require email if guest
        if (!$user) {
            $rules['customer_email'] = 'required|email';
        }

        $validated = $request->validate($rules);

        $items = $validated['items'];
        $data = $validated;
        $guestEmail = $validated['customer_email'] ?? null;
        
        // Add guest_email to data for Order creation
        if ($guestEmail) {
            $data['guest_email'] = $guestEmail;
        }

        // Recalculate Logic
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

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

        if ($totalDiscount > $subtotal) $totalDiscount = $subtotal;
        
        $data['discount_amount'] = $totalDiscount;
        $data['discount_code'] = !empty($couponDetails) ? implode(',', array_column($couponDetails, 'code')) : null;

        try {
            // Create Order
            // Suppress initial email for SePay (or other offsite gateways) to avoid "Order Success" confusion before payment
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
     * Get available coupons (Public/Active).
     */
    public function getAvailableCoupons(Request $request): JsonResponse
    {
        $user = $request->user();

        $coupons = ProductCoupon::where('is_active', true)
            ->where(function ($query) use ($user) {
                $query->where('is_public', true);
                
                if ($user) {
                    $query->orWhereJsonContains('restricted_emails', $user->email);
                }
            })
            ->where(function ($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->select('id', 'code', 'title', 'description', 'value', 'type', 'min_order_value', 'is_exclusive')
            ->get();
            
        return response()->json(['data' => $coupons]);
    }
}
