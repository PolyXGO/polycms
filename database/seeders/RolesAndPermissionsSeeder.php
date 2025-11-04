<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions for Posts
        $postPermissions = [
            'view-any post',
            'view post',
            'create post',
            'update post',
            'delete post',
            'restore post',
            'force-delete post',
        ];

        // Create permissions for Products
        $productPermissions = [
            'view-any product',
            'view product',
            'create product',
            'update product',
            'delete product',
            'restore product',
            'force-delete product',
        ];

        // Create permissions for Categories
        $categoryPermissions = [
            'view-any category',
            'view category',
            'create category',
            'update category',
            'delete category',
            'restore category',
            'force-delete category',
        ];

        // Create permissions for Tags
        $tagPermissions = [
            'view-any tag',
            'view tag',
            'create tag',
            'update tag',
            'delete tag',
            'restore tag',
            'force-delete tag',
        ];

        // Create permissions for Media
        $mediaPermissions = [
            'view-any media',
            'view media',
            'create media',
            'update media',
            'delete media',
            'restore media',
            'force-delete media',
        ];

        // Combine all permissions
        $allPermissions = array_merge(
            $postPermissions,
            $productPermissions,
            $categoryPermissions,
            $tagPermissions,
            $mediaPermissions
        );

        // Create permissions
        foreach ($allPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create Roles
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $editor = Role::firstOrCreate(['name' => 'editor']);
        $author = Role::firstOrCreate(['name' => 'author']);

        // Admin gets all permissions
        $admin->givePermissionTo(Permission::all());

        // Editor permissions
        $editor->givePermissionTo([
            'view-any post', 'view post', 'create post', 'update post', 'delete post', 'restore post',
            'view-any product', 'view product', 'create product', 'update product', 'delete product', 'restore product',
            'view-any category', 'view category', 'create category', 'update category', 'delete category',
            'view-any tag', 'view tag', 'create tag', 'update tag', 'delete tag',
            'view-any media', 'view media', 'create media', 'update media', 'delete media', 'restore media',
        ]);

        // Author permissions (limited)
        $author->givePermissionTo([
            'view-any post', 'view post', 'create post', 'update post', 'delete post',
            'view-any product', 'view product', 'create product', 'update product', 'delete product',
            'view-any category', 'view category',
            'view-any tag', 'view tag', 'create tag',
            'view-any media', 'view media', 'create media', 'update media', 'delete media',
        ]);
    }
}
