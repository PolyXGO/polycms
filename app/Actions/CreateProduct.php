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

            // Handle description: prioritize description_html (from Tiptap), fallback to rendering blocks
            if (isset($data['description_html']) && trim($data['description_html']) !== '' && 
                trim($data['description_html']) !== '<p></p>' && trim($data['description_html']) !== '<p><br></p>') {
                // Use HTML content directly from Tiptap editor
                // description_html is already set, no need to render
            } elseif (isset($data['description_blocks']) && is_array($data['description_blocks']) && !empty($data['description_blocks'])) {
                // Backward compatibility: render from blocks if description_html is not provided
                $data['description_html'] = $this->renderer->render($data['description_blocks']);
            } else {
                // Ensure description_html is null if not provided or empty
                $data['description_html'] = null;
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
