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

            // Handle description: prioritize description_html (from Tiptap), fallback to rendering blocks
            if (isset($data['description_html']) && trim($data['description_html']) !== '' && 
                trim($data['description_html']) !== '<p></p>' && trim($data['description_html']) !== '<p><br></p>') {
                // Use HTML content directly from Tiptap editor
                // description_html is already set, no need to render
            } elseif (isset($data['description_blocks']) && is_array($data['description_blocks']) && !empty($data['description_blocks'])) {
                // Backward compatibility: render from blocks if description_html is not provided
                $data['description_html'] = $this->renderer->render($data['description_blocks']);
            } else {
                // If description_html is not provided or empty, set to null
                if (isset($data['description_html'])) {
                    $data['description_html'] = null;
                }
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
                // First media is featured (is_primary = true)
                // Rest are gallery images
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
