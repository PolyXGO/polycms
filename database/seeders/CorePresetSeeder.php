<?php

namespace Database\Seeders;

use App\Models\CorePreset;
use App\Models\CorePresetCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CorePresetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Init default category
        $category = CorePresetCategory::firstOrCreate(
            ['name' => 'Default'],
            ['description' => 'System default presets']
        );

        $presets = [
            [
                'name' => 'Case Study (Read Story)',
                'description' => 'Elegant minimal outline button for Case Studies and Blogs.',
                'payload' => [
                    'style' => 'custom',
                    'alignment' => 'center',
                    'target' => '_self',
                    'label' => 'Action Button',
                    'bg_type' => 'solid',
                    'bg_color' => '#ffffff',
                    'text_color' => '#111827',
                    'border_color' => '#e5e7eb',
                    'hover_bg_type' => 'solid',
                    'hover_bg_color' => '#f9fafb',
                    'hover_text_color' => '#111827',
                    'hover_border_color' => '#d1d5db',
                    'border_radius' => '6px',
                    'inner_padding_top' => 12,
                    'inner_padding_right' => 24,
                    'inner_padding_bottom' => 12,
                    'inner_padding_left' => 24,
                    'font_size' => '0.875rem',
                    'font_weight' => '600'
                ]
            ],
            [
                'name' => 'Danger / Alert (Red)',
                'description' => 'Destructive or alert action button.',
                'payload' => [
                    'style' => 'custom',
                    'alignment' => 'center',
                    'target' => '_self',
                    'label' => 'Action Button',
                    'bg_type' => 'solid',
                    'bg_color' => '#ef4444',
                    'text_color' => '#ffffff',
                    'border_color' => '#ef4444',
                    'hover_bg_type' => 'solid',
                    'hover_bg_color' => '#dc2626',
                    'hover_text_color' => '#ffffff',
                    'hover_border_color' => '#dc2626',
                    'border_radius' => '6px',
                    'inner_padding_top' => 12,
                    'inner_padding_right' => 24,
                    'inner_padding_bottom' => 12,
                    'inner_padding_left' => 24,
                    'font_size' => '0.875rem',
                    'font_weight' => '600'
                ]
            ],
            [
                'name' => 'Dark Minimal (Black)',
                'description' => 'Modern minimal black button.',
                'payload' => [
                    'style' => 'custom',
                    'alignment' => 'center',
                    'target' => '_self',
                    'label' => 'Action Button',
                    'bg_type' => 'solid',
                    'bg_color' => '#111827',
                    'text_color' => '#ffffff',
                    'border_color' => '#111827',
                    'hover_bg_type' => 'solid',
                    'hover_bg_color' => '#374151',
                    'hover_text_color' => '#ffffff',
                    'hover_border_color' => '#374151',
                    'border_radius' => '6px',
                    'inner_padding_top' => 12,
                    'inner_padding_right' => 24,
                    'inner_padding_bottom' => 12,
                    'inner_padding_left' => 24,
                    'font_size' => '0.875rem',
                    'font_weight' => '600'
                ]
            ],
            [
                'name' => 'E-commerce (Buy Now)',
                'description' => 'High-conversion orange button perfect for "Buy Now" or "Add to Cart".',
                'payload' => [
                    'style' => 'custom',
                    'alignment' => 'center',
                    'target' => '_self',
                    'label' => 'Action Button',
                    'bg_type' => 'solid',
                    'bg_color' => '#f97316',
                    'text_color' => '#ffffff',
                    'border_color' => '#f97316',
                    'hover_bg_type' => 'solid',
                    'hover_bg_color' => '#ea580c',
                    'hover_text_color' => '#ffffff',
                    'hover_border_color' => '#ea580c',
                    'border_radius' => '6px',
                    'inner_padding_top' => 12,
                    'inner_padding_right' => 32,
                    'inner_padding_bottom' => 12,
                    'inner_padding_left' => 32,
                    'font_size' => '1rem',
                    'font_weight' => '700'
                ]
            ],
            [
                'name' => 'Lead Gen (Download PDF)',
                'description' => 'Action-oriented teal button for lead magnets and downloads.',
                'payload' => [
                    'style' => 'custom',
                    'alignment' => 'center',
                    'target' => '_self',
                    'label' => 'Action Button',
                    'bg_type' => 'solid',
                    'bg_color' => '#0d9488',
                    'text_color' => '#ffffff',
                    'border_color' => '#0d9488',
                    'hover_bg_type' => 'solid',
                    'hover_bg_color' => '#0f766e',
                    'hover_text_color' => '#ffffff',
                    'hover_border_color' => '#0f766e',
                    'border_radius' => '9999px',
                    'inner_padding_top' => 12,
                    'inner_padding_right' => 32,
                    'inner_padding_bottom' => 12,
                    'inner_padding_left' => 32,
                    'font_size' => '0.875rem',
                    'font_weight' => '600'
                ]
            ],
            [
                'name' => 'Premium (Book Demo)',
                'description' => 'Luxury gold/dark aesthetic for high-ticket services or demo bookings.',
                'payload' => [
                    'style' => 'custom',
                    'alignment' => 'center',
                    'target' => '_self',
                    'label' => 'Action Button',
                    'bg_type' => 'solid',
                    'bg_color' => '#111827',
                    'text_color' => '#fbbf24',
                    'border_color' => '#fbbf24',
                    'hover_bg_type' => 'solid',
                    'hover_bg_color' => '#fbbf24',
                    'hover_text_color' => '#111827',
                    'hover_border_color' => '#fbbf24',
                    'border_radius' => '4px',
                    'inner_padding_top' => 14,
                    'inner_padding_right' => 32,
                    'inner_padding_bottom' => 14,
                    'inner_padding_left' => 32,
                    'font_size' => '0.875rem',
                    'font_weight' => '600'
                ]
            ],
            [
                'name' => 'Primary Solid (Blue)',
                'description' => 'Standard primary call to action button.',
                'payload' => [
                    'style' => 'custom',
                    'alignment' => 'center',
                    'target' => '_self',
                    'label' => 'Action Button',
                    'bg_type' => 'solid',
                    'bg_color' => '#3b82f6',
                    'text_color' => '#ffffff',
                    'border_color' => '#3b82f6',
                    'hover_bg_type' => 'solid',
                    'hover_bg_color' => '#2563eb',
                    'hover_text_color' => '#ffffff',
                    'hover_border_color' => '#2563eb',
                    'border_radius' => '6px',
                    'inner_padding_top' => 12,
                    'inner_padding_right' => 24,
                    'inner_padding_bottom' => 12,
                    'inner_padding_left' => 24,
                    'font_size' => '0.875rem',
                    'font_weight' => '600'
                ]
            ],
            [
                'name' => 'SaaS (Start Free Trial)',
                'description' => 'Trust-building deep indigo button for SaaS trial signups.',
                'payload' => [
                    'style' => 'custom',
                    'alignment' => 'center',
                    'target' => '_self',
                    'label' => 'Action Button',
                    'bg_type' => 'solid',
                    'bg_color' => '#4f46e5',
                    'text_color' => '#ffffff',
                    'border_color' => '#4f46e5',
                    'hover_bg_type' => 'solid',
                    'hover_bg_color' => '#4338ca',
                    'hover_text_color' => '#ffffff',
                    'hover_border_color' => '#4338ca',
                    'border_radius' => '9999px',
                    'inner_padding_top' => 14,
                    'inner_padding_right' => 36,
                    'inner_padding_bottom' => 14,
                    'inner_padding_left' => 36,
                    'font_size' => '1rem',
                    'font_weight' => '600'
                ]
            ],
            [
                'name' => 'Secondary Outline (Blue)',
                'description' => 'Outline button for secondary actions.',
                'payload' => [
                    'style' => 'custom',
                    'alignment' => 'center',
                    'target' => '_self',
                    'label' => 'Action Button',
                    'bg_type' => 'solid',
                    'bg_color' => 'transparent',
                    'text_color' => '#3b82f6',
                    'border_color' => '#3b82f6',
                    'hover_bg_type' => 'solid',
                    'hover_bg_color' => '#3b82f6',
                    'hover_text_color' => '#ffffff',
                    'hover_border_color' => '#3b82f6',
                    'border_radius' => '6px',
                    'inner_padding_top' => 12,
                    'inner_padding_right' => 24,
                    'inner_padding_bottom' => 12,
                    'inner_padding_left' => 24,
                    'font_size' => '0.875rem',
                    'font_weight' => '600'
                ]
            ],
        ];

        foreach ($presets as $preset) {
            CorePreset::updateOrCreate(
                [
                    'category_id' => $category->id,
                    'name' => $preset['name'],
                    'type' => 'button_style'
                ],
                [
                    'description' => $preset['description'],
                    'payload' => $preset['payload'],
                    'is_global' => 1,
                    'is_system' => 1,
                ]
            );
        }
    }
}
