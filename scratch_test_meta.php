<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$post = \App\Models\Post::where('slug', 'like', '%demo-builder%')->first();
if ($post) {
    echo "Found post: {$post->title}\n";
    $post->setMeta('primary_category_id', '33');
    echo "Saved meta. New meta value: " . $post->getMeta('primary_category_id') . "\n";
} else {
    echo "Post not found\n";
}
