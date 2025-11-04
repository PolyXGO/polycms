<?php

declare(strict_types=1);

namespace App\Actions;

use App\Facades\Hook;
use App\Models\Product;
use App\Services\ContentRenderer;
use Illuminate\Support\Facades\DB;

class CreateProduct
{
    public function __construct(
        protected ContentRenderer $renderer
    ) {}

    /**
     * Create a new product
     */
    public function execute(array $data, array $categoryIds = [], array $tagIds = [], array $mediaIds = []): Product
    {
        return DB::transaction(function () use ($data, $categoryIds, $tagIds, $mediaIds) {
            // Apply filters before creating
            $data = Hook::applyFilters('product.create.data', $data);

            // Render content from blocks if provided
            if (isset($data['description_blocks']) && is_array($data['description_blocks'])) {
                $data['description_html'] = $this->renderer->render($data['description_blocks']);
            }

            $product = Product::create($data);

            // Attach categories, tags, and media
            if (!empty($categoryIds)) {
                $product->categories()->attach($categoryIds);
            }

            if (!empty($tagIds)) {
                $product->tags()->attach($tagIds);
            }

            if (!empty($mediaIds)) {
                $mediaData = [];
                foreach ($mediaIds as $index => $mediaId) {
                    $mediaData[$mediaId] = [
                        'is_primary' => $index === 0,
                        'order' => $index,
                    ];
                }
                $product->media()->attach($mediaData);
            }

            // Fire action hook
            Hook::doAction('product.saved', $product);

            return $product->load(['user', 'categories', 'tags', 'media']);
        });
    }
}
