<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Arr;
use Spatie\Permission\Models\Permission;

class PermissionRegistry
{
    /**
     * @var array<string, array<string, mixed>>
     */
    protected array $permissions = [];

    public function register(string $name, array $attributes = []): void
    {
        $key = strtolower($name);

        $this->permissions[$key] = array_merge(
            [
                'name' => $key,
                'label' => $attributes['label'] ?? $key,
                'group' => $attributes['group'] ?? 'core',
                'guard_name' => $attributes['guard_name'] ?? 'web',
                'module_owner' => $attributes['module_owner'] ?? null,
            ],
            Arr::except($attributes, ['label', 'group', 'guard_name', 'module_owner'])
        );
    }

    public function registerMany(array $permissions): void
    {
        foreach ($permissions as $name => $attributes) {
            if (is_int($name) && is_array($attributes) && isset($attributes['name'])) {
                $this->register($attributes['name'], $attributes);
            } elseif (is_string($name)) {
                $this->register($name, (array) $attributes);
            }
        }
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function all(): array
    {
        return $this->permissions;
    }

    public function syncDatabase(): void
    {
        foreach ($this->permissions as $definition) {
            $permission = Permission::firstOrCreate(
                [
                    'name' => $definition['name'],
                    'guard_name' => $definition['guard_name'],
                ]
            );

            if ($permission->isDirty()) {
                $permission->save();
            }
        }
    }
}

