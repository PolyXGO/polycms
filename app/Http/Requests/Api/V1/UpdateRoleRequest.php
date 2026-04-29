<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Role $role */
        $role = $this->route('role');

        return $role ? ($this->user()?->can('update', $role) ?? false) : false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('guard_name')) {
            $this->merge(['guard_name' => $this->string('guard_name')->toString()]);
        }
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $role = $this->route('role');

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($role?->id)],
            'guard_name' => ['sometimes', 'required', 'string', 'max:255'],
            'module_owner' => ['nullable', 'string', 'max:255'],
            'metadata' => ['nullable', 'array'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')],
        ];
    }
}

