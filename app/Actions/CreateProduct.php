<?php

declare(strict_types=1);

namespace App\Actions;

use App\Facades\Hook;
use App\Models\Product;
use App\Services\ContentRenderer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateProduct
{
    /**
     * @var array<string>|null
     */
    protected ?array $productColumns = null;

    public function __construct(
        protected ContentRenderer $renderer
    ) {}

    /**
     * Create a new product
     */
    public function execute(array $data, array $categoryIds = [], array $tagIds = [], array $mediaIds = [], array $brandIds = []): Product
    {
        return DB::transaction(function () use ($data, $categoryIds, $tagIds, $mediaIds, $brandIds) {
            // Apply filters before creating
            $data = Hook::applyFilters('product.create.data', $data);

            // Fire action hook before creation
            Hook::doAction('product.creating', $data);

            $serviceConfigs = $data['service_config'] ?? null;
            $descriptionBlocks = $data['description_blocks'] ?? null;
            $attributesData = $data['attributes'] ?? [];
            $variantsData = $data['variants'] ?? [];

            $product = Product::create($this->filterPersistableProductData($data));

            // Attach categories, tags, and media
            if (!empty($categoryIds)) {
                $product->categories()->attach($categoryIds);
            }

            if (!empty($tagIds)) {
                $product->tags()->attach($tagIds);
            }

            if (!empty($brandIds) && Schema::hasTable('product_brand')) {
                $product->brands()->attach($brandIds);
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

            // Save Service Configuration (Multiple Packages)
            if (is_array($serviceConfigs)) {
                if (isset($serviceConfigs['code']) || isset($serviceConfigs['name'])) {
                    $product->services()->create($serviceConfigs);
                } else {
                    foreach ($serviceConfigs as $config) {
                        if (is_array($config) && (!empty($config['code']) || !empty($config['name']))) {
                            $product->services()->create($config);
                        }
                    }
                }
                $product->load('services');
            }

            // Save Product Attributes & Variants
            if (!empty($attributesData)) {
                $syncData = [];
                foreach ($attributesData as $position => $attrData) {
                    if (!isset($attrData['attribute_id'])) continue;

                    $syncData[$attrData['attribute_id']] = [
                        'selected_value_ids' => json_encode($attrData['selected_value_ids'] ?? []),
                        'position' => $position,
                    ];
                }
                $product->variantAttributes()->sync($syncData);
                // Also update the flat index table for fast filtering
                $product->syncAttributeValueIndex($attributesData);
            }
            if (!empty($variantsData) && Schema::hasTable('product_variants')) {
                foreach ($variantsData as $position => $variantData) {
                    $attrValues = $variantData['attribute_values'] ?? [];
                    if (empty($attrValues)) continue;
                    $product->variants()->create([
                        'attribute_values' => $attrValues,
                        'sku' => $variantData['sku'] ?? null,
                        'price' => isset($variantData['price']) ? (float)$variantData['price'] : null,
                        'sale_price' => isset($variantData['sale_price']) ? (float)$variantData['sale_price'] : null,
                        'stock_quantity' => (int)($variantData['stock_quantity'] ?? 0),
                        'stock_status' => $variantData['stock_status'] ?? 'in_stock',
                        'manage_stock' => (bool)($variantData['manage_stock'] ?? true),
                        'image_id' => isset($variantData['image_id']) ? (int)$variantData['image_id'] : null,
                        'is_active' => (bool)($variantData['is_active'] ?? true),
                        'position' => $position,
                    ]);
                }
            }

            // Now handle description rendering with full product context
            if (!empty($descriptionBlocks)) {
                $descriptionHtml = $this->renderer
                    ->setContext(['product' => $product])
                    ->render($descriptionBlocks);
                
                $product->update(['description_html' => $descriptionHtml]);
            }

            // Fire action hook
            Hook::doAction('product.saved', $product);

            $relations = ['user', 'categories', 'tags', 'media', 'services', 'variants', 'variantAttributes.values'];
            if (Schema::hasTable('product_brand')) {
                $relations[] = 'brands';
            }

            return $product->load($relations);
        });
    }

    /**
     * Keep only columns that actually exist in current products table.
     *
     * This prevents SQL errors when code is ahead of DB migrations.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function filterPersistableProductData(array $data): array
    {
        $columns = $this->getProductColumns();

        return array_intersect_key($data, array_flip($columns));
    }

    /**
     * @return array<string>
     */
    protected function getProductColumns(): array
    {
        if ($this->productColumns !== null) {
            return $this->productColumns;
        }

        $this->productColumns = Schema::getColumnListing((new Product())->getTable());

        return $this->productColumns;
    }
}
