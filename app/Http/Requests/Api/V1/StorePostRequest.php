<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class StorePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Post::class);
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert empty strings to null for nullable fields
        $nullableFields = [
            'excerpt',
            'content_html',
            'meta_title',
            'meta_description',
            'meta_keywords',
            'og_image',
            'featured_image',
            'published_at',
            'scheduled_at',
        ];

        foreach ($nullableFields as $field) {
            if ($this->has($field) && $this->input($field) === '') {
                $this->merge([$field => null]);
            }
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:posts,slug'],
            'type' => ['required', Rule::in(['post', 'page', 'news'])],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'excerpt' => ['nullable', 'string', 'max:1000'],
            'content_html' => ['nullable', 'string'],
            'content_raw' => ['nullable', 'array'],
            'published_at' => ['nullable', 'date'],
            'scheduled_at' => ['nullable', 'date', 'after:now'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
            'og_image' => ['nullable', 'string', 'max:255'],
            'featured_image' => ['nullable', 'string', 'max:255'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['exists:categories,id'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['exists:post_tags,id'],
            'order' => ['nullable', 'integer', 'min:0'],
            'layout' => ['nullable', 'string', Rule::in(['default', 'fullwidth', 'landing', 'single-column'])],
            'show_featured_image' => ['nullable', 'boolean'],
            'meta_fields' => ['nullable', 'array'],
            'meta_fields.*' => ['nullable', 'string', 'max:65535'],
        ];

        if (
            Schema::hasTable('layout_assets')
            && Schema::hasTable('posts')
            && Schema::hasColumn('posts', 'layout_template_id')
        ) {
            $rules['layout_template_id'] = ['nullable', 'integer', Rule::exists('layout_assets', 'id')->where('kind', 'template')];
        }

        // Multi-theme template support
        if (Schema::hasColumn('posts', 'template_theme')) {
            $rules['template_theme'] = ['nullable', 'string', 'max:100'];
        }

        return $rules;
    }
}
