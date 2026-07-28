<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Language;
use App\Models\Category;
use App\Models\Post;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\User;

class MultilingualTestSeeder extends Seeder
{
    public function run(): void
    {
        // Skip seeding entirely if main-menu already exists and has menu items (indicating real data)
        $mainMenu = Menu::where('slug', 'main-menu')->first();
        if ($mainMenu && MenuItem::where('menu_id', $mainMenu->id)->exists()) {
            return;
        }
        // 1. Seed Vietnamese Language
        Language::updateOrCreate(
            ['code' => 'vi'],
            [
                'name' => 'Vietnamese',
                'native_name' => 'Tiếng Việt',
                'flag' => null,
                'is_default' => false,
                'is_active' => true,
                'direction' => 'ltr',
                'sort_order' => 2,
            ]
        );

        // 2. Find default Admin user
        $user = User::first() ?: User::create([
            'name' => 'Admin',
            'email' => 'admin@polycms.org',
            'password' => bcrypt('password'),
        ]);

        // 3. Create English Category
        $enGroup = (string) Str::uuid();
        $enCategory = Category::withoutGlobalScope('locale')->updateOrCreate(
            ['slug' => 'news', 'locale' => 'en'],
            [
                'name' => 'News',
                'type' => 'post',
                'translation_group_id' => $enGroup,
            ]
        );

        // 4. Create Vietnamese translation Category
        $viCategory = Category::withoutGlobalScope('locale')->updateOrCreate(
            ['slug' => 'tin-tuc', 'locale' => 'vi'],
            [
                'name' => 'Tin tức',
                'type' => 'post',
                'translation_group_id' => $enGroup,
            ]
        );

        // 5. Create English Post
        $postGroup = (string) Str::uuid();
        $enPost = Post::withoutGlobalScope('locale')->updateOrCreate(
            ['slug' => 'hello-world', 'locale' => 'en'],
            [
                'user_id' => $user->id,
                'title' => 'Hello World',
                'type' => 'post',
                'status' => 'published',
                'published_at' => now(),
                'translation_group_id' => $postGroup,
            ]
        );

        // 6. Create Vietnamese translation Post
        $viPost = Post::withoutGlobalScope('locale')->updateOrCreate(
            ['slug' => 'xin-chao-the-gioi', 'locale' => 'vi'],
            [
                'user_id' => $user->id,
                'title' => 'Xin chào thế giới',
                'type' => 'post',
                'status' => 'published',
                'published_at' => now(),
                'translation_group_id' => $postGroup,
            ]
        );

        // 7. Seed Main Menu
        $menu = Menu::updateOrCreate(
            ['slug' => 'main-menu'],
            [
                'name' => 'Main Menu',
                'location' => 'header',
            ]
        );

        // Clear existing menu items to prevent duplicates
        MenuItem::where('menu_id', $menu->id)->delete();

        // 8. Seed Menu Items
        // Item 1: Home (Custom Link)
        MenuItem::create([
            'menu_id' => $menu->id,
            'title' => 'Home',
            'url' => '/',
            'type' => 'custom',
            'order' => 1,
            'active' => true,
        ]);

        // Item 2: News Category Link (linkable_id = $enCategory->id)
        MenuItem::create([
            'menu_id' => $menu->id,
            'title' => 'News',
            'type' => 'custom',
            'linkable_id' => $enCategory->id,
            'linkable_type' => Category::class,
            'order' => 2,
            'active' => true,
        ]);

        // Item 3: Hello World Post Link (linkable_id = $enPost->id)
        MenuItem::create([
            'menu_id' => $menu->id,
            'title' => 'Hello World',
            'type' => 'custom',
            'linkable_id' => $enPost->id,
            'linkable_type' => Post::class,
            'order' => 3,
            'active' => true,
        ]);

        // Item 4: Language Switcher
        MenuItem::create([
            'menu_id' => $menu->id,
            'title' => 'Languages',
            'url' => '{"show_label":true}',
            'type' => 'language',
            'order' => 4,
            'active' => true,
        ]);
    }
}
