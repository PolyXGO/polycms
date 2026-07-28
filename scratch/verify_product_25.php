<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$product = App\Models\Product::find(25);
if (!$product) {
    $product = App\Models\Product::where('status', 'published')->first();
}

echo "=== PRODUCT INFO ===\n";
echo "ID: " . $product->id . "\n";
echo "Name: " . $product->name . "\n";
echo "Slug: " . $product->slug . "\n";
echo "Status: " . $product->status . "\n";
echo "Price: $" . $product->price . "\n";
echo "Frontend URL: " . $product->frontend_url . "\n";

// Save test offer rules for this product
$service = app(\Modules\Polyx\CommerceOffers\Services\CommerceOffersService::class);
$service->syncOffersForProduct($product, [
    'tiered_prices' => [
        ['min_sales' => 0, 'max_sales' => 10, 'price' => 11.40, 'label' => 'Early Bird 40% Off'],
        ['min_sales' => 11, 'max_sales' => 50, 'price' => 15.20, 'label' => 'Standard 20% Off'],
        ['min_sales' => 51, 'max_sales' => null, 'price' => 19.00, 'label' => 'Final Full Price'],
    ],
    'volume_discounts' => [
        ['min_qty' => 2, 'max_qty' => 4, 'discount_type' => 'percentage', 'discount_value' => 10, 'label' => 'Buy 2-4: 10% Off'],
        ['min_qty' => 5, 'max_qty' => 9, 'discount_type' => 'percentage', 'discount_value' => 20, 'label' => 'Buy 5-9: 20% Off'],
        ['min_qty' => 10, 'max_qty' => null, 'discount_type' => 'percentage', 'discount_value' => 30, 'label' => 'Buy 10+: 30% Off'],
    ],
    '_force_clear_offers' => true,
]);

echo "\n=== DATABASE VERIFICATION (IMMEDIATELY AFTER SAVE) ===\n";
$tieredInDb = \Modules\Polyx\CommerceOffers\Models\ProductTieredPrice::where('product_id', $product->id)->get();
echo "Tiered Prices in DB: " . $tieredInDb->count() . "\n";
foreach ($tieredInDb as $t) {
    echo " - " . $t->min_sales . "-" . ($t->max_sales ?? '∞') . " => $" . $t->price . " (" . $t->label . ")\n";
}

$volumeInDb = \Modules\Polyx\CommerceOffers\Models\ProductVolumeDiscount::where('product_id', $product->id)->get();
echo "Volume Discounts in DB: " . $volumeInDb->count() . "\n";
foreach ($volumeInDb as $v) {
    echo " - Buy " . $v->min_qty . "-" . ($v->max_qty ?? '+') . " => -" . $v->discount_value . "% (" . $v->label . ")\n";
}

// Simulate main product update (e.g. updating product title or status without touch offers)
echo "\n=== SIMULATING MAIN PRODUCT UPDATE (WITHOUT OFFERS PAYLOAD) ===\n";
$updateAction = app(\App\Actions\UpdateProduct::class);
$updateAction->execute($product, [
    'name' => $product->name,
    'price' => $product->price,
    'stock_status' => $product->stock_status,
]);

echo "\n=== DATABASE VERIFICATION (AFTER MAIN PRODUCT UPDATE) ===\n";
$tieredAfterUpdate = \Modules\Polyx\CommerceOffers\Models\ProductTieredPrice::where('product_id', $product->id)->get();
echo "Tiered Prices in DB after product update: " . $tieredAfterUpdate->count() . "\n";

$volumeAfterUpdate = \Modules\Polyx\CommerceOffers\Models\ProductVolumeDiscount::where('product_id', $product->id)->get();
echo "Volume Discounts in DB after product update: " . $volumeAfterUpdate->count() . "\n";

// Test Frontend URL
$frontendUrl = "http://127.0.0.1:8000" . $product->frontend_url;
echo "\n=== TESTING FRONTEND URL: " . $frontendUrl . " ===\n";
$ch = curl_init($frontendUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$html = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Frontend HTTP Status: " . $httpCode . "\n";
if (str_contains($html, 'commerce-offers-banner-block') || str_contains($html, 'Early Bird 40% Off')) {
    echo "SUCCESS: Offers Banner rendered cleanly on frontend page!\n";
} else {
    echo "WARNING: Offers Banner text not found in HTML. Length: " . strlen($html) . "\n";
}
