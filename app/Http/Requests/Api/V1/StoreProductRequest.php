<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Product::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    protected function prepareForValidation(): void
    {
        $nullableFields = [
            'short_description',
            'description_html',
            'meta_title',
            'meta_description',
            'meta_keywords',
            'refund_policy_note',
            'published_at',
            'scheduled_at',
        ];

        foreach ($nullableFields as $field) {
            if ($this->has($field) && $this->input($field) === '') {
                $this->merge([$field => null]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', Rule::in(['product', 'service', 'digital', 'variable'])],
            'slug' => ['required', 'string', 'max:255', 'unique:products,slug'],
            'sku' => ['nullable', 'string', 'max:255', 'unique:products,sku'],
            'short_description' => ['nullable', 'string', 'max:1000'],
            'description_blocks' => ['nullable', 'array'],
            'description_html' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0', 'lt:price'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'stock_status' => ['required', Rule::in(['in_stock', 'out_of_stock', 'on_backorder'])],
            'stock_quantity' => ['nullable', 'integer', 'min:0'],
            'manage_stock' => ['nullable', 'boolean'],
            'stock_low_threshold' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'featured' => ['nullable', 'boolean'],
            'allow_refund' => ['nullable', 'boolean'],
            'refund_window_days' => ['nullable', 'integer', 'min:0', 'max:3650'],
            'refund_policy_note' => ['nullable', 'string', 'max:1000'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'length' => ['nullable', 'numeric', 'min:0'],
            'width' => ['nullable', 'numeric', 'min:0'],
            'height' => ['nullable', 'numeric', 'min:0'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['exists:product_categories,id'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['exists:product_tags,id'],
            'brands' => ['nullable', 'array'],
            'brands.*' => ['exists:product_brands,id'],
            'media_ids' => ['nullable', 'array'],
            'media_ids.*' => ['exists:media,id'],
            'order' => ['nullable', 'integer', 'min:0'],
            'published_at' => ['nullable', 'date'],
            'scheduled_at' => ['nullable', 'date', 'after:now'],
            'layout' => ['nullable', 'string', Rule::in(['default', 'fullwidth', 'landing'])],
            'settings' => ['nullable', 'array'],
            'service_config' => ['nullable', 'array'],
            'service_config.*.name' => ['required', 'string', 'max:255'],
            'service_config.*.code' => ['required', 'string', 'max:50'],
            'service_config.*.access_type' => ['nullable', 'string', Rule::in(['permanent', 'limited_time', 'subscription'])],
            'service_config.*.duration_value' => ['nullable', 'integer', 'min:1'],
            'service_config.*.duration_unit' => ['nullable', 'string', Rule::in(['day', 'week', 'month', 'year'])],
            'service_config.*.is_recurring' => ['nullable', 'boolean'],
            'service_config.*.trial_period_days' => ['nullable', 'integer', 'min:0'],
            'service_config.*.price' => ['nullable', 'numeric', 'min:0'],
            'service_config.*.license_policy' => ['nullable', 'string'],
            'service_config.*.capabilities' => ['nullable', 'array'],
            'template_theme' => ['nullable', 'string', 'max:100'],
            // Variants & Attributes
            'attributes' => ['nullable', 'array'],
            'attributes.*.attribute_id' => ['required_with:attributes', 'integer', 'exists:product_attributes,id'],
            'attributes.*.position' => ['nullable', 'integer'],
            'attributes.*.selected_value_ids' => ['nullable', 'array'],
            'attributes.*.selected_value_ids.*' => ['integer', 'exists:product_attribute_values,id'],
            'attributes.*.is_specification' => ['nullable', 'boolean'],
            'variants' => ['nullable', 'array'],
            'variants.*.attribute_values' => ['required_with:variants', 'array'],
            'variants.*.sku' => ['nullable', 'string', 'max:255'],
            'variants.*.price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.sale_price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.stock_quantity' => ['nullable', 'integer', 'min:0'],
            'variants.*.stock_status' => ['nullable', 'string', Rule::in(['in_stock', 'out_of_stock', 'on_backorder'])],
            'variants.*.manage_stock' => ['nullable', 'boolean'],
            'variants.*.is_active' => ['nullable', 'boolean'],
            'variants.*.is_default' => ['nullable', 'boolean'],
            'variants.*.image_id' => ['nullable', 'integer', 'exists:media,id'],
        ];
    }
}
