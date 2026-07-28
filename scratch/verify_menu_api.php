<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = App\Models\User::where('email', 'admin@polycms.org')->first();
Illuminate\Support\Facades\Auth::login($user);

$adminMenuController = app(App\Http\Controllers\Api\V1\AdminMenuController::class);
$request = Illuminate\Http\Request::create('/api/v1/admin/menu', 'GET');
$request->setUserResolver(fn() => $user);

$adminMenuResponse = $adminMenuController->index($request);
$adminMenuData = $adminMenuResponse->getData(true);

echo "Admin Menu API items:\n";
foreach ($adminMenuData['data'] as $item) {
    echo "- " . ($item['key'] ?? '') . " : " . ($item['label'] ?? '') . "\n";
}

$topbarMenuController = app(App\Http\Controllers\Api\V1\TopbarMenuController::class);
$topbarRequest = Illuminate\Http\Request::create('/api/v1/topbar-menu', 'GET');
$topbarRequest->setUserResolver(fn() => $user);

$topbarResponse = $topbarMenuController->index($topbarRequest);
$topbarData = $topbarResponse->getData(true);

echo "\nTopbar Menu API items:\n";
foreach ($topbarData['data'] as $item) {
    echo "- " . ($item['id'] ?? '') . " : " . ($item['label'] ?? '') . "\n";
}
