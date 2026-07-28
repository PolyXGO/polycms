<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class LayoutAssetCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Marketing', 'slug' => 'marketing', 'description' => 'Marketing blocks and banners'],
            ['name' => 'Features', 'slug' => 'features', 'description' => 'Feature showcases and grids'],
            ['name' => 'Pricing', 'slug' => 'pricing', 'description' => 'Pricing tables and plans'],
            ['name' => 'Content', 'slug' => 'content', 'description' => 'Text, images, and content sections'],
            ['name' => 'E-Commerce', 'slug' => 'ecommerce', 'description' => 'Products, carts, and shop layouts'],
            ['name' => 'Contact', 'slug' => 'contact', 'description' => 'Contact forms and info'],
            ['name' => 'Social Proof', 'slug' => 'social-proof', 'description' => 'Testimonials, logos, and reviews'],
            ['name' => 'Navigation', 'slug' => 'navigation', 'description' => 'Headers, footers, and menus'],
            ['name' => 'Media', 'slug' => 'media', 'description' => 'Video, galleries, and media blocks'],
            ['name' => 'Default', 'slug' => 'default', 'description' => 'Default theme layouts'],
            ['name' => 'General', 'slug' => 'general', 'description' => 'General purpose components'],
            ['name' => 'Landing Page', 'slug' => 'landing', 'description' => 'Landing page templates'],
            ['name' => 'Blog', 'slug' => 'blog', 'description' => 'Blog and news layouts'],
            ['name' => 'Product', 'slug' => 'product', 'description' => 'Product detail layouts'],
            ['name' => 'Portfolio', 'slug' => 'portfolio', 'description' => 'Portfolio showcases'],
            ['name' => 'Documentation', 'slug' => 'documentation', 'description' => 'Documentation layouts'],
            ['name' => 'Coming Soon', 'slug' => 'coming-soon', 'description' => 'Coming soon pages'],
            ['name' => 'Real Estate', 'slug' => 'real-estate', 'description' => 'Property and real estate layouts'],
            ['name' => 'Education', 'slug' => 'education', 'description' => 'Courses and educational layouts'],
            ['name' => 'Healthcare', 'slug' => 'healthcare', 'description' => 'Clinic and healthcare layouts'],
            ['name' => 'Corporate B2B', 'slug' => 'corporate', 'description' => 'Professional B2B layouts'],
            ['name' => 'F&B Restaurant', 'slug' => 'restaurant', 'description' => 'Food and restaurant layouts'],
        ];

        foreach ($categories as $index => $categoryData) {
            Category::firstOrCreate(
                ['type' => 'layout_asset', 'slug' => $categoryData['slug']],
                [
                    'name' => $categoryData['name'],
                    'description' => $categoryData['description'],
                    'order' => $index,
                ]
            );
        }
    }
}
