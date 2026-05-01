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
            LanguageSeeder::class,
            RolesAndPermissionsSeeder::class,
            CorePaymentMethodsSeeder::class,
        ]);

        // Admin user is now created during web installation wizard.
        
        // Seed default site language
        \Illuminate\Support\Facades\DB::table('settings')->updateOrInsert(
            ['key' => 'site_language'],
            [
                'value' => 'en',
                'group' => 'general',
                'type' => 'string',
                'label' => 'Site Language',
                'description' => 'Default language for the site',
                'autoload' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        \Illuminate\Support\Facades\DB::table('settings')->updateOrInsert(
            ['key' => 'site_language_direction'],
            [
                'value' => 'ltr',
                'group' => 'general',
                'type' => 'string',
                'label' => 'Text Direction',
                'description' => 'LTR or RTL',
                'autoload' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
