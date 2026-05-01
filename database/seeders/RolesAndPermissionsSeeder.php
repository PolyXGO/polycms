<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $definitions = config('permissions.definitions', []);
        $allPermissions = [];

        foreach ($definitions as $definition) {
            foreach ($definition['permissions'] ?? [] as $permission) {
                $name = is_array($permission) ? $permission['name'] : $permission;
                if ($name) {
                    $allPermissions[] = [
                        'name' => $name,
                        'guard_name' => $permission['guard_name'] ?? 'web',
                    ];
                }
            }
        }

        foreach ($allPermissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission['name'],
                'guard_name' => $permission['guard_name'],
            ]);
        }

        // Create Roles
        $admin = Role::firstOrCreate(['name' => 'admin'], ['guard_name' => 'web', 'is_system' => true]);
        $editor = Role::firstOrCreate(['name' => 'editor'], ['guard_name' => 'web', 'is_system' => true]);
        $author = Role::firstOrCreate(['name' => 'author'], ['guard_name' => 'web', 'is_system' => true]);
        $customer = Role::firstOrCreate(['name' => 'customer'], ['guard_name' => 'web', 'is_system' => true]);

        $systemRoles = [$admin, $editor, $author, $customer];
        foreach ($systemRoles as $role) {
            if (!$role->is_system) {
                $role->is_system = true;
                $role->save();
            }
        }

        // Admin gets all permissions
        $admin->givePermissionTo(Permission::all());

        // Editor permissions
        $editor->givePermissionTo([
            'view-any post', 'view post', 'create post', 'update post', 'delete post', 'restore post',
            'view-any product', 'view product', 'create product', 'update product', 'delete product', 'restore product',
            'view-any category', 'view category', 'create category', 'update category', 'delete category',
            'view-any tag', 'view tag', 'create tag', 'update tag', 'delete tag',
            'view-any media', 'view media', 'create media', 'update media', 'delete media', 'restore media',
            'view widgets', 'manage widgets',
            'view themes', 'manage theme options',
            'view settings',
            'view-any order', 'view order', 'update order', 'refund order',
            'view inventory logs',
        ]);

        // Author permissions (limited)
        $author->givePermissionTo([
            'view-any post', 'view post', 'create post', 'update post', 'delete post',
            'view-any product', 'view product', 'create product', 'update product', 'delete product',
            'view-any category', 'view category',
            'view-any tag', 'view tag', 'create tag',
            'view-any media', 'view media', 'create media', 'update media', 'delete media',
            'view widgets',
        ]);

        $customer->givePermissionTo([
            'view api tokens', 'create api tokens', 'update api tokens', 'delete api tokens',
        ]);
    }
}
