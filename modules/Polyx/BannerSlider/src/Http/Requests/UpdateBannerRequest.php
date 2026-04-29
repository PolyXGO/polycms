<?php

declare(strict_types=1);

namespace Modules\Polyx\BannerSlider\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBannerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Routes are protected by auth:sanctum middleware, so authenticated users can update banners
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'type' => ['sometimes', 'nullable', 'in:image,text'],
            'image_id' => ['sometimes', 'nullable', 'exists:media,id'],
            'link' => ['sometimes', 'nullable', 'url', 'max:500'],
            'link_target' => ['sometimes', 'nullable', 'in:_self,_blank'],
            'link_rel' => ['sometimes', 'nullable', 'string', 'max:255'],
            'order' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'active' => ['sometimes', 'nullable', 'boolean'],
            'start_date' => ['sometimes', 'nullable', 'date'],
            'end_date' => ['sometimes', 'nullable', 'date', 'after_or_equal:start_date'],
            'description' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'content' => ['sometimes', 'nullable', 'string'],
            'button_text' => ['sometimes', 'nullable', 'string', 'max:255'],
            'button_link' => ['sometimes', 'nullable', 'url', 'max:500'],
            'button_target' => ['sometimes', 'nullable', 'in:_self,_blank'],
            'button_rel' => ['sometimes', 'nullable', 'string', 'max:255'],
            'background_color' => ['sometimes', 'nullable', 'string', 'max:7', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'button_bg_color' => ['sometimes', 'nullable', 'string', 'max:7', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'button_text_color' => ['sometimes', 'nullable', 'string', 'max:7', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'text_color' => ['sometimes', 'nullable', 'string', 'max:7', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'gradient_color' => ['sometimes', 'nullable', 'string', 'max:7', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'gradient_degree' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:360'],
            'button_gradient_color' => ['sometimes', 'nullable', 'string', 'max:7', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'button_gradient_degree' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:360'],
            'button_hover_bg_color' => ['sometimes', 'nullable', 'string', 'max:7', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'button_hover_text_color' => ['sometimes', 'nullable', 'string', 'max:7', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'button_hover_gradient_color' => ['sometimes', 'nullable', 'string', 'max:7', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'countdown_enabled' => ['sometimes', 'nullable', 'boolean'],
            'countdown_date' => ['sometimes', 'nullable', 'date'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'image_id.exists' => 'Selected image does not exist.',
            'link.url' => 'Link must be a valid URL.',
            'end_date.after_or_equal' => 'End date must be after or equal to start date.',
        ];
    }
}
