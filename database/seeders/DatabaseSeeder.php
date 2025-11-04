<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles and permissions first
        $this->call([
            RolesAndPermissionsSeeder::class,
        ]);

        // Create admin user
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@polycms.test',
        ]);
        $admin->assignRole('admin');

        // Create editor user
        $editor = User::factory()->create([
            'name' => 'Editor User',
            'email' => 'editor@polycms.test',
        ]);
        $editor->assignRole('editor');

        // Create author user
        $author = User::factory()->create([
            'name' => 'Author User',
            'email' => 'author@polycms.test',
        ]);
        $author->assignRole('author');
    }
}
