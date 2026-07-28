<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$products = App\Models\Product::where('status', 'published')->get();
echo "Total published products: " . $products->count() . "\n";

foreach ($products as $p) {
    echo "ID: " . $p->id . " | Name: " . $p->name . " | Slug: " . $p->slug . " | URL: " . $p->frontend_url . "\n";
}
