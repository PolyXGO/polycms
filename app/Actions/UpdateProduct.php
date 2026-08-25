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

                    $config = $this->normalizeServiceConfig($config);

                    // Try to find existing by id first, then by code or name
                    $existing = null;
                    if (!empty($config['id'])) {
                        $existing = $product->services()->where('id', $config['id'])->first();
                    }
                    if (!$existing && !empty($config['code'])) {
                        $existing = $product->services()->where('code', $config['code'])->first();
                    }
                    if (!$existing && !empty($config['name'])) {
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

                // Delete services that are no longer in the list safely (skip those referenced in order_items)
                $toDelete = $product->services()->whereNotIn('id', $processedIds)->get();
                foreach ($toDelete as $serviceToDelete) {
                    $isReferencedInOrders = \App\Models\Ecommerce\OrderItem::where('service_id', $serviceToDelete->id)->exists();
                    if (!$isReferencedInOrders) {
                        $serviceToDelete->delete();
                    }
                }
                
                // Refresh services relationship so renderer sees new data
                $product->unsetRelation('services');
                unset($data['service_config']);
            }

            // Handle description: prefer description_html if provided, fallback to description_blocks
            if (isset($data['description_html']) && $data['description_html'] !== null && trim($data['description_html']) !== '') {
                $trimmed = trim($data['description_html']);
                if ($trimmed === '<p></p>' || $trimmed === '<p><br></p>') {
                    $data['description_html'] = null;
                }
            } elseif (!empty($data['description_blocks'])) {
                $data['description_html'] = $this->renderer
                    ->setContext(['product' => $product])
                    ->render($data['description_blocks']);
            }

            // Preserve external sales & rating stats in settings if present on model and not explicitly provided in payload
            if (isset($data['settings']) && is_array($data['settings'])) {
                $existingSettings = $product->settings ?? [];
                foreach (['external_sales', 'external_rating', 'external_rating_count'] as $statKey) {
                    if (isset($existingSettings[$statKey]) && !array_key_exists($statKey, $data['settings'])) {
                        $data['settings'][$statKey] = $existingSettings[$statKey];
                    }
                }
            }

            // Multilingual: Primary language is the Single Source of Truth for purchase options & CTA summary
            $primaryProduct = $product->getPrimaryProduct();
            $isPrimaryProduct = !$primaryProduct || $primaryProduct->id === $product->id;
            if (!$isPrimaryProduct && isset($data['settings']) && is_array($data['settings'])) {
                if (isset($primaryProduct->settings['purchase_options'])) {
                    $data['settings']['purchase_options'] = $primaryProduct->settings['purchase_options'];
                }
                if (isset($primaryProduct->settings['purchase_cta_summary'])) {
                    $data['settings']['purchase_cta_summary'] = $primaryProduct->settings['purchase_cta_summary'];
                }
            }

            $product->update($this->filterPersistableProductData($data));

            // Sync product type, pricing, service packages, and purchase options across all translations in group
            if (!empty($product->translation_group_id)) {
                Product::withoutGlobalScope('locale')
                    ->where('translation_group_id', $product->translation_group_id)
                    ->where('id', '!=', $product->id)
                    ->update([
                        'type' => $product->type,
                        'price' => $product->price,
                        'sale_price' => $product->sale_price,
                        'cost_price' => $product->cost_price,
                    ]);

                // Sync service packages across all translations in group (syncing price, duration, settings while retaining localized labels if present)
                $mainServices = $product->services;
                if ($mainServices && $mainServices->isNotEmpty()) {
                    $siblingProducts = Product::withoutGlobalScope('locale')
                        ->where('translation_group_id', $product->translation_group_id)
                        ->where('id', '!=', $product->id)
                        ->get();

                    foreach ($siblingProducts as $sibling) {
                        $processedSiblingIds = [];
                        foreach ($mainServices as $mainService) {
                            $siblingService = $sibling->services()->where('code', $mainService->code)->first();
                            $syncData = [
                                'code' => $mainService->code,
                                'price' => $mainService->price,
                                'access_type' => $mainService->access_type,
                                'duration_value' => $mainService->duration_value,
                                'duration_unit' => $mainService->duration_unit,
                                'is_recurring' => $mainService->is_recurring,
                                'trial_period_days' => $mainService->trial_period_days,
                                'capabilities' => $mainService->capabilities,
                                'license_policy' => $mainService->license_policy,
                            ];
                            if ($siblingService) {
                                if (empty($siblingService->name)) {
                                    $syncData['name'] = $mainService->name;
                                }
                                $siblingService->update($syncData);
                                $processedSiblingIds[] = $siblingService->id;
                            } else {
                                $syncData['name'] = $mainService->name;
                                $newSib = $sibling->services()->create($syncData);
                                $processedSiblingIds[] = $newSib->id;
                            }
                        }
                        $toDelete = $sibling->services()->whereNotIn('id', $processedSiblingIds)->get();
                        foreach ($toDelete as $delSib) {
                            $isReferenced = \App\Models\Ecommerce\OrderItem::where('service_id', $delSib->id)->exists();
                            if (!$isReferenced) {
                                $delSib->delete();
                            }
                        }
                    }
                }

                // If primary product is updated, sync purchase_options and purchase_cta_summary across all siblings
                if ($isPrimaryProduct) {
                    $siblingProducts = Product::withoutGlobalScope('locale')
                        ->where('translation_group_id', $product->translation_group_id)
                        ->where('id', '!=', $product->id)
                        ->get();

                    foreach ($siblingProducts as $sibling) {
                        $sibSettings = $sibling->settings ?? [];
                        $hasChanges = false;
                        if (isset($product->settings['purchase_options'])) {
                            $sibSettings['purchase_options'] = $product->settings['purchase_options'];
                            $hasChanges = true;
                        }
                        if (isset($product->settings['purchase_cta_summary'])) {
                            $sibSettings['purchase_cta_summary'] = $product->settings['purchase_cta_summary'];
                            $hasChanges = true;
                        }
                        if ($hasChanges) {
                            $sibling->settings = $sibSettings;
                            $sibling->saveQuietly();
                        }
                    }
                }
            }

            // Sync CommerceOffers rules if provided in payload
            if (class_exists(\Modules\Polyx\CommerceOffers\Services\CommerceOffersService::class)) {
                try {
                    app(\Modules\Polyx\CommerceOffers\Services\CommerceOffersService::class)->syncOffersForProduct($product, $data);
                } catch (\Throwable $e) {}
            }

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
            Hook::doAction('product.saved', $product, $data);

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

    /**
     * Normalize service config data before saving.
     * Wraps string license_policy into array format for JSON column.
     */
    protected function normalizeServiceConfig(array $config): array
    {
        if (isset($config['license_policy']) && is_string($config['license_policy'])) {
            $config['license_policy'] = ['type' => $config['license_policy']];
        }
        return $config;
    }
}

