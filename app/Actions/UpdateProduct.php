<?php

declare(strict_types=1);

namespace App\Actions;

use App\Facades\Hook;
use App\Models\Product;
use App\Services\ContentRenderer;
use Illuminate\Support\Facades\DB;

class UpdateProduct
{
    public function __construct(
        protected ContentRenderer $renderer
    ) {}

    /**
     * Update an existing product
     */
    public function execute(Product $product, array $data, ?array $categoryIds = null, ?array $tagIds = null, ?array $mediaIds = null): Product
    {
        return DB::transaction(function () use ($product, $data, $categoryIds, $tagIds, $mediaIds) {
            // Apply filters before updating
            $data = Hook::applyFilters('product.update.data', $data, $product);

            // Render content from blocks if provided
            if (isset($data['description_blocks']) && is_array($data['description_blocks'])) {
                $data['description_html'] = $this->renderer->render($data['description_blocks']);
            }

            $product->update($data);

            // Sync categories if provided
            if ($categoryIds !== null) {
                $product->categories()->sync($categoryIds);
            }

            // Sync tags if provided
            if ($tagIds !== null) {
                $product->tags()->sync($tagIds);
            }

            // Sync media if provided
            if ($mediaIds !== null) {
                $mediaData = [];
                foreach ($mediaIds as $index => $mediaId) {
                    $mediaData[$mediaId] = [
                        'is_primary' => $index === 0,
                        'order' => $index,
                    ];
                }
                $product->media()->sync($mediaData);
            }

            // Fire action hook
            Hook::doAction('product.saved', $product);

            return $product->load(['user', 'categories', 'tags', 'media']);
        });
    }
}
