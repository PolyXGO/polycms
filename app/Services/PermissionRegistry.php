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
        if ($this->isPermissionList($attributes)) {
            foreach ($attributes as $definition) {
                $permissionName = $definition['name'] ?? $definition['key'] ?? null;

                if (!$permissionName) {
                    continue;
                }

                $this->register($permissionName, array_merge($definition, [
                    'group' => $definition['group'] ?? $name,
                    'module_owner' => $definition['module_owner'] ?? $name,
                ]));
            }

            return;
        }

        $key = strtolower($name);
        $label = $attributes['label'] ?? $attributes['description'] ?? $key;

        $this->permissions[$key] = array_merge(
            [
                'name' => $key,
                'label' => $label,
                'group' => $attributes['group'] ?? 'core',
                'guard_name' => $attributes['guard_name'] ?? 'web',
                'module_owner' => $attributes['module_owner'] ?? null,
            ],
            Arr::except($attributes, ['key', 'name', 'label', 'group', 'guard_name', 'module_owner'])
        );
    }

    public function registerMany(array $permissions): void
    {
        foreach ($permissions as $name => $attributes) {
            if (is_int($name) && is_array($attributes) && (isset($attributes['name']) || isset($attributes['key']))) {
                $this->register($attributes['name'] ?? $attributes['key'], $attributes);
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

    protected function isPermissionList(array $attributes): bool
    {
        if ($attributes === [] || !array_is_list($attributes)) {
            return false;
        }

        foreach ($attributes as $definition) {
            if (!is_array($definition) || (!isset($definition['name']) && !isset($definition['key']))) {
                return false;
            }
        }

        return true;
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
