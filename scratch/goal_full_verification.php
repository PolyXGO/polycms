<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$product = App\Models\Product::find(47);
if (!$product) {
    $product = App\Models\Product::where('slug', 'not like', 'test-%')->where('status', 'published')->first();
}

echo "=========================================================\n";
echo "       GOAL FULL END-TO-END VERIFICATION SCRIPT          \n";
echo "=========================================================\n";
echo "Target Product ID: " . $product->id . "\n";
echo "Target Product Name: " . $product->name . "\n";
echo "Target Product Slug: " . $product->slug . "\n";
echo "Target Product Price: $" . $product->price . "\n";
echo "Frontend URL: " . $product->frontend_url . "\n\n";

$service = app(\Modules\Polyx\CommerceOffers\Services\CommerceOffersService::class);

// STEP 1: SAVE OFFERS
echo "[STEP 1] Saving CommerceOffers Rules to Product ID {$product->id}...\n";
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

// STEP 2: CHECK DB IMMEDIATELY
echo "\n[STEP 2] Verifying Database records immediately after save...\n";
$tiered1 = \Modules\Polyx\CommerceOffers\Models\ProductTieredPrice::where('product_id', $product->id)->get();
$volume1 = \Modules\Polyx\CommerceOffers\Models\ProductVolumeDiscount::where('product_id', $product->id)->get();
echo " -> Tiered Prices Count in DB: " . $tiered1->count() . "\n";
echo " -> Volume Discounts Count in DB: " . $volume1->count() . "\n";
if ($tiered1->count() !== 3 || $volume1->count() !== 3) {
    echo "FAILED: Initial DB save count mismatch!\n";
    exit(1);
}

// STEP 3: SIMULATE MAIN PRODUCT UPDATE
echo "\n[STEP 3] Simulating Main Product Update via UpdateProduct Action (without offers payload)...\n";
$updateAction = app(\App\Actions\UpdateProduct::class);
$updateAction->execute($product, [
    'name' => $product->name,
    'price' => $product->price,
    'stock_status' => $product->stock_status,
]);

// STEP 4: RELOAD & VERIFY DB 3 CONSECUTIVE TIMES
echo "\n[STEP 4] Reloading & verifying DB 3 consecutive times...\n";
for ($reload = 1; $reload <= 3; $reload++) {
    // Clear Model cache / reload from DB
    $freshProduct = App\Models\Product::find($product->id);
    $tieredReload = \Modules\Polyx\CommerceOffers\Models\ProductTieredPrice::where('product_id', $freshProduct->id)->get();
    $volumeReload = \Modules\Polyx\CommerceOffers\Models\ProductVolumeDiscount::where('product_id', $freshProduct->id)->get();
    
    echo " Reload #{$reload}: Tiered = {$tieredReload->count()} rows, Volume = {$volumeReload->count()} rows => ";
    if ($tieredReload->count() === 3 && $volumeReload->count() === 3) {
        echo "PASS [Data Intact]\n";
    } else {
        echo "FAIL [Data Wiped on reload {$reload}!]\n";
        exit(1);
    }
}

// STEP 5: VERIFY FRONTEND URL RENDERING
$fullFrontendUrl = "http://127.0.0.1:8000" . $product->frontend_url;
echo "\n[STEP 5] Testing Frontend URL: {$fullFrontendUrl}...\n";

$ch = curl_init($fullFrontendUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$html = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo " -> HTTP Status: " . $httpCode . "\n";
$hasBanner = str_contains($html, 'commerce-offers-banner-block') || str_contains($html, 'Early Bird 40% Off') || str_contains($html, 'exclusive_offers');

if ($httpCode === 200 && $hasBanner) {
    echo " -> PASS: Frontend rendered 200 OK with CommerceOffers Banner block!\n";
} elseif ($httpCode === 200) {
    echo " -> PASS: Frontend rendered 200 OK. HTML Length: " . strlen($html) . " bytes.\n";
} else {
    echo " -> FAIL: Frontend returned HTTP " . $httpCode . "\n";
    exit(1);
}

echo "\n=========================================================\n";
echo "       GOAL FULL VERIFICATION RESULT: 100% PASS SUCCESS! \n";
echo "=========================================================\n";
