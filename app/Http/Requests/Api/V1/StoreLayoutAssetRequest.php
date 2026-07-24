<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Models\LayoutAsset;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class StoreLayoutAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', LayoutAsset::class);
    }

    protected function prepareForValidation(): void
    {
        $nullableFields = ['description', 'preview_image'];

        foreach ($nullableFields as $field) {
            if ($this->has($field) && $this->input($field) === '') {
                $this->merge([$field => null]);
            }
        }
    }

    public function rules(): array
    {
        $slugRules = ['nullable', 'string', 'max:255'];
        if (Schema::hasTable('layout_assets')) {
            $slugRules[] = 'unique:layout_assets,slug';
        }

        return [
            'kind' => ['required', Rule::in(['part', 'template'])],
            'name' => ['required', 'string', 'max:255'],
            'slug' => $slugRules,
            'category' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:4000'],
            'layout' => ['nullable', Rule::in(['landing'])],
            'content_raw' => ['nullable', 'array'],
            'preview_image' => ['nullable', 'string', 'max:2048'],
            'meta' => ['nullable', 'array'],
            'applies_to' => ['nullable', 'array'],
            'applies_to.*' => ['string', Rule::in(['page', 'post', 'news'])],
        ];
    }
}
