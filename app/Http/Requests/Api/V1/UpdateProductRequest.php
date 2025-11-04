<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('product'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $productId = $this->route('product');

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('products', 'slug')->ignore($productId)],
            'sku' => ['nullable', 'string', 'max:255', Rule::unique('products', 'sku')->ignore($productId)],
            'short_description' => ['nullable', 'string', 'max:1000'],
            'description_blocks' => ['nullable', 'array'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'stock_status' => ['sometimes', 'required', Rule::in(['in_stock', 'out_of_stock', 'on_backorder'])],
            'stock_quantity' => ['nullable', 'integer', 'min:0'],
            'manage_stock' => ['nullable', 'boolean'],
            'stock_low_threshold' => ['nullable', 'integer', 'min:0'],
            'status' => ['sometimes', 'required', Rule::in(['draft', 'published', 'archived'])],
            'featured' => ['nullable', 'boolean'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'length' => ['nullable', 'numeric', 'min:0'],
            'width' => ['nullable', 'numeric', 'min:0'],
            'height' => ['nullable', 'numeric', 'min:0'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['exists:categories,id'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['exists:tags,id'],
            'media_ids' => ['nullable', 'array'],
            'media_ids.*' => ['exists:media,id'],
            'order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
