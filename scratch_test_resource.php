<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$post = \App\Models\Post::where('slug', 'like', '%demo-builder%')->first();
$resource = new \App\Http\Resources\Api\V1\PostResource($post);
$data = $resource->resolve();
echo json_encode($data['meta_fields']);
