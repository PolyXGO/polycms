<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Ecommerce\ProductLicense;
use App\Models\Ecommerce\UserSubscription;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\V1\LicenseController;
use Illuminate\Support\Facades\DB;

$key = 'MTX-HLJ6-VZKT-DWRH';
echo "========================================================\n";
echo "TESTING LICENSE KEY SECURITY: {$key}\n";
echo "========================================================\n\n";

// 1. Ensure User & Product & Subscription exist for testing
$user = User::first();
if (!$user) {
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);
}

$product = DB::table('products')->first();
if (!$product) {
    $productId = DB::table('products')->insertGetId([
        'name' => 'MTElements Premium',
        'slug' => 'mtelements-premium',
        'price' => 49.00,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
} else {
    $productId = $product->id;
}

$service = DB::table('product_services')->first();
if (!$service) {
    $serviceId = DB::table('product_services')->insertGetId([
        'product_id' => $productId,
        'code' => 'std_license',
        'name' => 'Standard License',
        'price' => 49.00,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
} else {
    $serviceId = $service->id;
}

$sub = UserSubscription::where('user_id', $user->id)->first();
if (!$sub) {
    $sub = UserSubscription::create([
        'user_id' => $user->id,
        'product_id' => $productId,
        'service_id' => $serviceId,
        'starts_at' => now(),
        'expires_at' => now()->addYear(),
        'status' => 'active',
    ]);
}

// Ensure Project & ProjectRelease exist for MTElements check-update test
if (class_exists(\Modules\Polyx\ProjectHub\Models\Project::class)) {
    $project = \Modules\Polyx\ProjectHub\Models\Project::where('project_code', 'MTElements')->first();
    if (!$project) {
        $project = \Modules\Polyx\ProjectHub\Models\Project::create([
            'name' => 'Polyx MTElements',
            'slug' => 'mtelements',
            'project_code' => 'MTElements',
            'platform' => 'polycms',
        ]);
    }
    
    // Link project to product
    DB::table('project_products')->updateOrInsert(
        ['project_id' => $project->id, 'product_id' => $productId]
    );

    $release = \Modules\Polyx\ProjectHub\Models\ProjectRelease::where('project_id', $project->id)->first();
    if (!$release) {
        \Modules\Polyx\ProjectHub\Models\ProjectRelease::create([
            'project_id' => $project->id,
            'version' => '1.2.0',
            'title' => 'v1.2.0 Release',
            'summary' => 'Added activation token security.',
            'status' => 'published',
            'download_url' => 'local://releases/mtelements-1.2.0.zip',
            'released_at' => now(),
        ]);
    }
}

// 2. Find or create ProductLicense
$license = ProductLicense::with('activations')->where('license_key', $key)->first();
if (!$license) {
    $license = ProductLicense::create([
        'subscription_id' => $sub->id,
        'license_key' => $key,
        'max_activations' => 5,
        'status' => 'active'
    ]);
}

echo "License Details:\n";
echo "  - Key: " . $license->license_key . "\n";
echo "  - Status: " . $license->status . "\n";
echo "  - Max Activations: " . $license->max_activations . "\n";
echo "  - Current Activations Count: " . $license->activations->count() . "\n\n";

$controller = app(LicenseController::class);

// ---------------------------------------------------------------
// TEST CASE 1: Client chính chủ (polycms.org) kích hoạt License lần đầu
// ---------------------------------------------------------------
echo "--- TEST CASE 1: Site polycms.org kích hoạt License (`/api/v1/licenses/activate`) ---\n";
$req1 = Request::create('/api/v1/licenses/activate', 'POST', [
    'license_key' => $key,
    'domain' => 'polycms.org',
]);
$res1 = $controller->activatePublic($req1);
$data1 = json_decode($res1->getContent(), true);

echo "Response Status: " . $res1->getStatusCode() . "\n";
echo "Response Message: " . ($data1['message'] ?? '') . "\n";

$activationToken = $data1['data']['activation_token'] ?? null;
echo "=> Token bí mật cấp cho site polycms.org: [" . ($activationToken ?: 'NONE') . "]\n\n";


// ---------------------------------------------------------------
// TEST CASE 2: Fake request từ máy ngoài dùng Key `MTX-HLJ6-VZKT-DWRH` + domain `polycms.org` (KHÔNG CÓ TOKEN BẢO MẬT)
// ---------------------------------------------------------------
echo "--- TEST CASE 2: Request gia mạo từ máy ngoài (Dùng Key + domain polycms.org nhưng KHÔNG CÓ TOKEN) ---\n";
$req2 = Request::create('/api/v1/licenses/check-update', 'GET', [
    'module' => 'Polyx.MTElements',
    'version' => '1.0.0',
    'license_key' => $key,
    'domain' => 'polycms.org',
    // KHÔNG TRUYỀN activation_token
]);
$res2 = $controller->checkUpdatePublic($req2);
$data2 = json_decode($res2->getContent(), true);

echo "Response Status: " . $res2->getStatusCode() . "\n";
echo "Download URL: '" . ($data2['download_url'] ?? '') . "'\n";
if (empty($data2['download_url'])) {
    echo "==> KẾT QUẢ: HỆ THỐNG ĐÃ CHẶN THÀNH CÔNG! Không cấp link tải cho Fake Request (do thiếu Token).\n\n";
} else {
    echo "==> CẢNH BÁO: Đã lấy được URL: " . $data2['download_url'] . "\n\n";
}


// ---------------------------------------------------------------
// TEST CASE 3: Fake request dùng Key + domain `polycms.org` với TOKEN GIẢ
// ---------------------------------------------------------------
echo "--- TEST CASE 3: Request giả mạo dùng TOKEN GIẢ/SAI ('invalid_hacker_token') ---\n";
$req3 = Request::create('/api/v1/licenses/check-update', 'GET', [
    'module' => 'Polyx.MTElements',
    'version' => '1.0.0',
    'license_key' => $key,
    'domain' => 'polycms.org',
    'activation_token' => 'invalid_hacker_token_123456'
]);
$res3 = $controller->checkUpdatePublic($req3);
$data3 = json_decode($res3->getContent(), true);

echo "Response Status: " . $res3->getStatusCode() . "\n";
echo "Download URL: '" . ($data3['download_url'] ?? '') . "'\n";
if (empty($data3['download_url'])) {
    echo "==> KẾT QUẢ: HỆ THỐNG ĐÃ CHẶN THÀNH CÔNG! Token giả bị bác bỏ.\n\n";
} else {
    echo "==> CẢNH BÁO: Đã lấy được URL: " . $data3['download_url'] . "\n\n";
}


// ---------------------------------------------------------------
// TEST CASE 4: Site CHÍNH CHỦ polycms.org gửi request với ĐÚNG TOKEN BẢO MẬT
// ---------------------------------------------------------------
echo "--- TEST CASE 4: Site CHÍNH CHỦ polycms.org gửi request với ĐÚNG TOKEN BẢO MẬT ---\n";
$req4 = Request::create('/api/v1/licenses/check-update', 'GET', [
    'module' => 'Polyx.MTElements',
    'version' => '1.0.0',
    'license_key' => $key,
    'domain' => 'polycms.org',
    'activation_token' => $activationToken
]);
$res4 = $controller->checkUpdatePublic($req4);
$data4 = json_decode($res4->getContent(), true);

echo "Response Status: " . $res4->getStatusCode() . "\n";
echo "Download URL: '" . ($data4['download_url'] ?? '') . "'\n";
if (!empty($data4['download_url'])) {
    echo "==> KẾT QUẢ: HỢP LỆ 100%! Site chính chủ polycms.org nhận được Signed Download URL thành công.\n\n";
} else {
    echo "==> LỖI: Không lấy được URL\n\n";
}

// Clean up test data
$license->activations()->delete();
$license->delete();

echo "========================================================\n";
echo "TẤT CẢ KỊCH BẢN ĐÃ KIỂM TRA XONG.\n";
echo "========================================================\n";
