<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'label' => $this->metadata['label'] ?? $this->name,
            'guard_name' => $this->guard_name,
            'is_system' => (bool) $this->is_system,
            'module_owner' => $this->module_owner,
            'metadata' => $this->metadata ?? [],
            'permissions' => $this->permissions->pluck('name')->values(),
            'users_count' => $this->whenCounted('users'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

