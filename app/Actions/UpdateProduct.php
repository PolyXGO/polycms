<?php

declare(strict_types=1);

namespace App\Actions;

use App\Facades\Hook;
use App\Models\Product;
use App\Services\ContentRenderer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateProduct
{
    /**
     * @var array<string>|null
     */
    protected ?array $productColumns = null;

    public function __construct(
        protected ContentRenderer $renderer
    ) {}

    /**
     * Update an existing product
     */
    public function execute(Product $product, array $data, ?array $categoryIds = null, ?array $tagIds = null, ?array $mediaIds = null, ?array $brandIds = null): Product
    {
        return DB::transaction(function () use ($product, $data, $categoryIds, $tagIds, $mediaIds, $brandIds) {
            // Apply filters before updating
            $data = Hook::applyFilters('product.update.data', $data, $product);

            // Fire action hook before updating
            Hook::doAction('product.updating', $product, $data);

            // Update Service Configuration (Multiple Packages) before rendering HTML
            if (isset($data['service_config']) && is_array($data['service_config'])) {
                $serviceConfigs = $data['service_config'];
                
                // Handle single object for backward compatibility
                if (isset($serviceConfigs['code']) || isset($serviceConfigs['name'])) {
                    $serviceConfigs = [$serviceConfigs];
                }

                $processedIds = [];

                foreach ($serviceConfigs as $config) {
                    if (!is_array($config) || (empty($config['code']) && empty($config['name']))) {
                        continue;
                    }

                    // Try to find existing by code or name
                    $existing = null;
                    if (!empty($config['code'])) {
                        $existing = $product->services()->where('code', $config['code'])->first();
                    } elseif (!empty($config['name'])) {
                        $existing = $product->services()->where('name', $config['name'])->first();
                    }

                    if ($existing) {
                        $existing->update($config);
                        $processedIds[] = $existing->id;
                    } else {
                        $newService = $product->services()->create($config);
                        $processedIds[] = $newService->id;
                    }
                }

                // Delete services that are no longer in the list
                $product->services()->whereNotIn('id', $processedIds)->delete();
                
                // Refresh services relationship so renderer sees new data
                $product->unsetRelation('services');
                unset($data['service_config']);
            }

            // Handle description: Always prefer rendering from description_blocks if available
            // This ensures backend renderers (Blade, hooks) are used for complex blocks
            if (!empty($data['description_blocks'])) {
                // ContentRenderer now handles the context
                $data['description_html'] = $this->renderer
                    ->setContext(['product' => $product])
                    ->render($data['description_blocks']);
            } elseif (isset($data['description_html'])) {
                if (trim($data['description_html']) === '' || trim($data['description_html']) === '<p></p>' || trim($data['description_html']) === '<p><br></p>') {
                    $data['description_html'] = null;
                }
            }

            $product->update($this->filterPersistableProductData($data));

            // Sync categories if provided
            if ($categoryIds !== null) {
                $product->categories()->sync($categoryIds);
            }

            // Sync tags if provided
            if ($tagIds !== null) {
                $product->tags()->sync($tagIds);
            }

            if ($brandIds !== null && Schema::hasTable('product_brand')) {
                $product->brands()->sync($brandIds);
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

            // Sync Product Attributes & Variants (for variable products)
            if (isset($data['attributes']) && is_array($data['attributes'])) {
                $this->syncAttributes($product, $data['attributes']);
            }
            if (isset($data['variants']) && is_array($data['variants'])) {
                $this->syncVariants($product, $data['variants']);
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

    /**
     * Sync product attributes (variant dimensions) using global attributes via pivot
     */
    protected function syncAttributes(Product $product, array $attributes): void
    {
        $syncData = [];

        foreach ($attributes as $position => $attrData) {
            if (!isset($attrData['attribute_id'])) continue;

            $syncData[$attrData['attribute_id']] = [
                'selected_value_ids' => json_encode($attrData['selected_value_ids'] ?? []),
                'position' => $position,
                'is_specification' => (bool) ($attrData['is_specification'] ?? true),
            ];
        }

        $product->variantAttributes()->sync($syncData);
        // Update the flat index table
        $product->syncAttributeValueIndex($attributes);
    }

    /**
     * Sync product variants
     */
    protected function syncVariants(Product $product, array $variants): void
    {
        if (!Schema::hasTable('product_variants')) {
            return;
        }

        $keptIds = [];

        foreach ($variants as $position => $variantData) {
            $attrValues = $variantData['attribute_values'] ?? [];
            if (empty($attrValues)) continue;

            // Try to find existing variant by attribute_values match
            $existing = $product->variants()->get()->first(function ($v) use ($attrValues) {
                $existingAttrs = $v->attribute_values ?? [];
                return json_encode($existingAttrs) === json_encode($attrValues);
            });

            $fields = [
                'attribute_values' => $attrValues,
                'sku' => $variantData['sku'] ?? null,
                'price' => isset($variantData['price']) ? (float)$variantData['price'] : null,
                'sale_price' => isset($variantData['sale_price']) ? (float)$variantData['sale_price'] : null,
                'stock_quantity' => (int)($variantData['stock_quantity'] ?? 0),
                'stock_status' => $variantData['stock_status'] ?? 'in_stock',
                'manage_stock' => (bool)($variantData['manage_stock'] ?? true),
                'image_id' => isset($variantData['image_id']) ? (int)$variantData['image_id'] : null,
                'is_active' => (bool)($variantData['is_active'] ?? true),
                'is_default' => (bool)($variantData['is_default'] ?? false),
                'position' => $position,
            ];

            if ($existing) {
                $existing->update($fields);
                $keptIds[] = $existing->id;
            } else {
                $newVariant = $product->variants()->create($fields);
                $keptIds[] = $newVariant->id;
            }
        }

        // Remove orphaned variants
        if (!empty($keptIds)) {
            $product->variants()->whereNotIn('id', $keptIds)->delete();
        } elseif (empty($variants)) {
            $product->variants()->delete();
        }
    }
}

