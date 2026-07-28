<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$ch = curl_init('http://127.0.0.1:8000/products/mtelements-for-polycms');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$html = curl_exec($ch);
curl_close($ch);

$count = substr_count($html, 'commerce-offers-banner-block');
echo "Commerce Offers Banner count in frontend HTML: " . $count . "\n";
if ($count === 1) {
    echo "SUCCESS: Banner renders EXACTLY ONCE (No double rendering)!\n";
} else {
    echo "WARNING: Banner count is " . $count . "\n";
}
