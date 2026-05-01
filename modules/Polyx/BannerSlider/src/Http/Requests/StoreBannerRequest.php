<?php

declare(strict_types=1);

namespace Modules\Polyx\BannerSlider\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBannerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Routes are protected by auth:sanctum middleware, so authenticated users can create banners
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'in:image,text'],
            'image_id' => ['required_if:type,image', 'nullable', 'exists:media,id'],
            'link' => ['nullable', 'url', 'max:500'],
            'link_target' => ['nullable', 'in:_self,_blank'],
            'link_rel' => ['nullable', 'string', 'max:255'],
            'order' => ['nullable', 'integer', 'min:0'], // Will be auto-set based on created_at if not provided
            'active' => ['nullable', 'boolean'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'description' => ['nullable', 'string', 'max:1000'],
            'content' => ['nullable', 'string'],
            'button_text' => ['nullable', 'string', 'max:255'],
            'button_link' => ['nullable', 'url', 'max:500'],
            'button_target' => ['nullable', 'in:_self,_blank'],
            'button_rel' => ['nullable', 'string', 'max:255'],
            'background_color' => ['nullable', 'string', 'max:7', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'button_bg_color' => ['nullable', 'string', 'max:7', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'button_text_color' => ['nullable', 'string', 'max:7', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'text_color' => ['nullable', 'string', 'max:7', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'gradient_color' => ['nullable', 'string', 'max:7', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'gradient_degree' => ['nullable', 'integer', 'min:0', 'max:360'],
            'button_gradient_color' => ['nullable', 'string', 'max:7', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'button_gradient_degree' => ['nullable', 'integer', 'min:0', 'max:360'],
            'button_hover_bg_color' => ['nullable', 'string', 'max:7', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'button_hover_text_color' => ['nullable', 'string', 'max:7', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'button_hover_gradient_color' => ['nullable', 'string', 'max:7', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'countdown_enabled' => ['nullable', 'boolean'],
            'countdown_date' => ['nullable', 'date', 'after:now'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'image_id.required_if' => 'Banner image is required for image type banners.',
            'image_id.exists' => 'Selected image does not exist.',
            'link.url' => 'Link must be a valid URL.',
            'button_link.url' => 'Button link must be a valid URL.',
            'end_date.after_or_equal' => 'End date must be after or equal to start date.',
            'type.in' => 'Banner type must be either image or text.',
            'gradient_degree.between' => 'Gradient degree must be between 0 and 360.',
        ];
    }
}
