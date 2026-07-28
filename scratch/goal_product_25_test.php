<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$product = App\Models\Product::find(25);

echo "=========================================================\n";
echo "       PRODUCT 25 PERSISTENCE & RELOAD TEST              \n";
echo "=========================================================\n";
echo "Target Product ID: " . $product->id . "\n";

$service = app(\Modules\Polyx\CommerceOffers\Services\CommerceOffersService::class);

// STEP 1: SAVE OFFERS
$service->syncOffersForProduct($product, [
    'tiered_prices' => [
        ['min_sales' => 0, 'max_sales' => 10, 'price' => 11.40, 'label' => 'Early Bird 40% Off'],
        ['min_sales' => 11, 'max_sales' => 50, 'price' => 15.20, 'label' => 'Standard 20% Off'],
        ['min_sales' => 51, 'max_sales' => null, 'price' => 19.00, 'label' => 'Final Full Price'],
    ],
    'volume_discounts' => [
        ['min_qty' => 2, 'max_qty' => 4, 'discount_type' => 'percentage', 'discount_value' => 10, 'label' => 'Buy 2-4: 10% Off'],
    ],
    '_force_clear_offers' => true,
]);

// STEP 2: SIMULATE 5 CONSECUTIVE MAIN PRODUCT SAVES (e.g. clicking Update in admin UI)
echo "\n[SIMULATING 5 CONSECUTIVE MAIN PRODUCT SAVES]\n";
$updateAction = app(\App\Actions\UpdateProduct::class);

for ($i = 1; $i <= 5; $i++) {
    $updateAction->execute($product, [
        'name' => $product->name,
        'price' => $product->price,
    ]);
    
    $tieredCount = \Modules\Polyx\CommerceOffers\Models\ProductTieredPrice::where('product_id', $product->id)->count();
    $volumeCount = \Modules\Polyx\CommerceOffers\Models\ProductVolumeDiscount::where('product_id', $product->id)->count();
    
    echo " Save #{$i}: Tiered DB rows = {$tieredCount}, Volume DB rows = {$volumeCount} => ";
    if ($tieredCount === 3 && $volumeCount === 1) {
        echo "PASS [INTACT]\n";
    } else {
        echo "FAIL [WIPED!]\n";
        exit(1);
    }
}

echo "\nPRODUCT 25 VERIFICATION: 100% SUCCESS!\n";
