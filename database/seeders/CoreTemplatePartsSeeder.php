<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Services\LayoutAssetManager;
use Illuminate\Support\Str;

/**
 * Core Template Parts Seeder
 *
 * Registers ~25 default template parts across multiple categories.
 * Each part is composed entirely from core landing elements.
 */
class CoreTemplatePartsSeeder
{
    public function __construct(
        protected LayoutAssetManager $manager
    ) {}

    public function seed(): void
    {
        $this->seedMarketing();
        $this->seedFeatures();
        $this->seedPricing();
        $this->seedContent();
        $this->seedSocialProof();
        $this->seedContact();
        $this->seedMedia();
        $this->seedEcommerce();
    }

    // ═══════════════════════════════════════════════
    //  Marketing (5 parts)
    // ═══════════════════════════════════════════════

    protected function seedMarketing(): void
    {
        // 1. Hero Gradient
        $this->manager->registerPart('core.hero_gradient', [
            'name' => 'Hero Gradient',
            'slug' => 'hero-gradient',
            'description' => 'A vibrant gradient hero section with heading, subtext, and dual CTA buttons.',
            'category' => 'marketing',
            'content_raw' => $this->doc([
                $this->block('section', [
                    'bg_color' => '',
                    'bg_image' => '',
                    'padding' => 'py-20',
                    'style' => 'background: linear-gradient(135deg, #312e81 0%, #4f46e5 50%, #7c3aed 100%); color: #ffffff; text-align: center;',
                    'blocks' => [
                        ['type' => 'heading', 'data' => ['text' => 'Build Something Amazing', 'level' => 1, 'alignment' => 'center', 'font_weight' => 'font-extrabold', 'color' => '#ffffff']],
                        ['type' => 'spacer', 'data' => ['height' => 16]],
                        ['type' => 'text', 'data' => ['content' => 'The modern platform for creators, entrepreneurs, and businesses. Launch faster, scale smarter.', 'font_size' => 'text-lg', 'alignment' => 'center', 'color' => 'rgba(199, 210, 254, 0.9)']],
                        ['type' => 'spacer', 'data' => ['height' => 32]],
                        ['type' => 'row', 'data' => [
                            'columns' => 2, 'layout_preset' => 'halves', 'column_widths' => ['1/2', '1/2'],
                            'gap' => 'gap-4', 'vertical_align' => 'center',
                            'columns_data' => [
                                ['blocks' => [['type' => 'button', 'data' => ['label' => 'Get Started Free', 'url' => '#', 'style' => 'primary', 'size' => 'px-8 py-4 text-lg', 'alignment' => 'right']]]],
                                ['blocks' => [['type' => 'button', 'data' => ['label' => 'Watch Demo', 'url' => '#', 'style' => 'ghost', 'size' => 'px-8 py-4 text-lg', 'alignment' => 'left', 'bg_color' => 'rgba(255,255,255,0.15)', 'text_color' => '#ffffff', 'hover_bg_color' => 'rgba(255,255,255,0.25)']]]],
                            ],
                        ]],
                    ],
                ]),
            ]),
        ]);

        // 2. Hero Split Image
        $this->manager->registerPart('core.hero_split', [
            'name' => 'Hero Split Image',
            'slug' => 'hero-split-image',
            'description' => 'A two-column hero with text on one side and an image on the other.',
            'category' => 'marketing',
            'content_raw' => $this->doc([
                $this->block('row', [
                    'columns' => 2, 'layout_preset' => 'halves', 'column_widths' => ['1/2', '1/2'],
                    'gap' => 'gap-12', 'vertical_align' => 'center',
                    'columns_data' => [
                        ['blocks' => [
                            ['type' => 'heading', 'data' => ['text' => 'Transform Your Business Today', 'level' => 1, 'alignment' => 'left', 'font_weight' => 'font-extrabold', 'color' => '']],
                            ['type' => 'spacer', 'data' => ['height' => 12]],
                            ['type' => 'text', 'data' => ['content' => 'Streamline operations, boost productivity, and scale your revenue with our all-in-one platform.', 'font_size' => 'text-lg', 'alignment' => 'left', 'color' => 'var(--theme-body-muted-color)']],
                            ['type' => 'spacer', 'data' => ['height' => 24]],
                            ['type' => 'button', 'data' => ['label' => 'Start Free Trial', 'url' => '#', 'style' => 'primary', 'size' => 'px-8 py-4 text-lg', 'alignment' => 'left']],
                        ]],
                        ['blocks' => [
                            ['type' => 'image', 'data' => ['src' => 'https://images.unsplash.com/photo-1551434678-e076c223a692?auto=format&fit=crop&w=800&q=80', 'alt' => 'Business dashboard', 'width' => 'w-full', 'alignment' => 'center', 'border_radius' => 'rounded-2xl']],
                        ]],
                    ],
                ]),
            ]),
        ]);

        // 3. CTA Gradient
        $this->manager->registerPart('core.cta_gradient', [
            'name' => 'CTA Gradient',
            'slug' => 'cta-gradient',
            'description' => 'A bold gradient call-to-action with centered heading and button.',
            'category' => 'marketing',
            'content_raw' => $this->doc([
                $this->block('section', [
                    'bg_color' => '', 'bg_image' => '', 'padding' => 'py-20',
                    'style' => 'background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); color: #ffffff; text-align: center;',
                    'blocks' => [
                        ['type' => 'heading', 'data' => ['text' => 'Ready to Get Started?', 'level' => 2, 'alignment' => 'center', 'font_weight' => 'font-bold', 'color' => '#ffffff']],
                        ['type' => 'spacer', 'data' => ['height' => 12]],
                        ['type' => 'text', 'data' => ['content' => 'Join thousands of businesses already growing with our platform. No credit card required.', 'font_size' => 'text-lg', 'alignment' => 'center', 'color' => 'rgba(199, 210, 254, 0.9)']],
                        ['type' => 'spacer', 'data' => ['height' => 28]],
                        ['type' => 'button', 'data' => ['label' => 'Start Free Trial', 'url' => '#', 'style' => 'primary', 'size' => 'px-8 py-4 text-lg', 'alignment' => 'center', 'bg_color' => '#ffffff', 'text_color' => '#4f46e5', 'hover_bg_color' => '#f0f0f0']],
                    ],
                ]),
            ]),
        ]);

        // 4. Newsletter CTA
        $this->manager->registerPart('core.cta_newsletter', [
            'name' => 'Newsletter CTA',
            'slug' => 'cta-newsletter',
            'description' => 'A newsletter signup section with heading, description, and email form.',
            'category' => 'marketing',
            'content_raw' => $this->doc([
                $this->block('section', [
                    'bg_color' => 'var(--theme-surface-color)', 'bg_image' => '', 'padding' => 'py-16',
                    'blocks' => [
                        ['type' => 'heading', 'data' => ['text' => 'Stay in the Loop', 'level' => 2, 'alignment' => 'center', 'font_weight' => 'font-bold', 'color' => '']],
                        ['type' => 'spacer', 'data' => ['height' => 8]],
                        ['type' => 'text', 'data' => ['content' => 'Subscribe to our newsletter and get the latest updates, tips, and exclusive offers delivered to your inbox.', 'font_size' => 'text-base', 'alignment' => 'center', 'color' => 'var(--theme-body-muted-color)']],
                        ['type' => 'spacer', 'data' => ['height' => 24]],
                        ['type' => 'html_block', 'data' => ['html' => '<div style="max-width:480px;margin:0 auto;display:flex;gap:10px"><input type="email" placeholder="Enter your email" style="flex:1;padding:12px 16px;border:1px solid #d1d5db;border-radius:10px;font-size:15px;outline:none"><button style="padding:12px 24px;background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#fff;border:none;border-radius:10px;font-weight:700;font-size:15px;cursor:pointer;white-space:nowrap">Subscribe</button></div>', 'wrap_raw' => false]],
                    ],
                ]),
            ]),
        ]);

        // 5. Announcement Banner
        $this->manager->registerPart('core.banner_announcement', [
            'name' => 'Announcement Banner',
            'slug' => 'banner-announcement',
            'description' => 'A compact announcement banner with text and action button.',
            'category' => 'marketing',
            'content_raw' => $this->doc([
                $this->block('section', [
                    'bg_color' => '#4f46e5', 'bg_image' => '', 'padding' => 'py-4',
                    'blocks' => [
                        ['type' => 'row', 'data' => [
                            'columns' => 2, 'layout_preset' => 'sidebar-right', 'column_widths' => ['2/3', '1/3'],
                            'gap' => 'gap-4', 'vertical_align' => 'center',
                            'columns_data' => [
                                ['blocks' => [
                                    ['type' => 'text', 'data' => ['content' => '🎉 New: We just launched v2.0 with 50+ new features. Check it out!', 'font_size' => 'text-base', 'alignment' => 'left', 'color' => '#ffffff']],
                                ]],
                                ['blocks' => [
                                    ['type' => 'button', 'data' => ['label' => 'Learn More →', 'url' => '#', 'style' => 'ghost', 'size' => 'px-4 py-2 text-sm', 'alignment' => 'right', 'bg_color' => 'rgba(255,255,255,0.15)', 'text_color' => '#ffffff', 'hover_bg_color' => 'rgba(255,255,255,0.25)']],
                                ]],
                            ],
                        ]],
                    ],
                ]),
            ]),
        ]);
    }

    // ═══════════════════════════════════════════════
    //  Features & Benefits (4 parts)
    // ═══════════════════════════════════════════════

    protected function seedFeatures(): void
    {
        // 6. Features 3 Columns
        $this->manager->registerPart('core.features_3col', [
            'name' => 'Features 3 Columns',
            'slug' => 'features-3-columns',
            'description' => 'Three-column feature cards with images, titles, and descriptions.',
            'category' => 'features',
            'content_raw' => $this->doc([
                $this->block('heading', ['text' => 'Everything You Need', 'level' => 2, 'alignment' => 'center', 'font_weight' => 'font-bold', 'color' => '']),
                $this->block('spacer', ['height' => 8]),
                $this->block('text', ['content' => 'Powerful features to help you grow your business faster.', 'font_size' => 'text-lg', 'alignment' => 'center', 'color' => 'var(--theme-body-muted-color)']),
                $this->block('spacer', ['height' => 40]),
                $this->block('row', [
                    'columns' => 3, 'layout_preset' => 'thirds', 'column_widths' => ['1/3', '1/3', '1/3'],
                    'gap' => 'gap-8', 'vertical_align' => 'start',
                    'columns_data' => [
                        ['blocks' => [
                            ['type' => 'image', 'data' => ['src' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=400&q=80', 'alt' => 'Analytics', 'width' => 'w-full', 'alignment' => 'center', 'border_radius' => 'rounded-xl']],
                            ['type' => 'spacer', 'data' => ['height' => 16]],
                            ['type' => 'heading', 'data' => ['text' => 'Advanced Analytics', 'level' => 3, 'alignment' => 'center', 'font_weight' => 'font-bold', 'color' => '']],
                            ['type' => 'text', 'data' => ['content' => 'Track key metrics and make data-driven decisions with real-time dashboards.', 'font_size' => 'text-sm', 'alignment' => 'center', 'color' => 'var(--theme-body-muted-color)']],
                        ]],
                        ['blocks' => [
                            ['type' => 'image', 'data' => ['src' => 'https://images.unsplash.com/photo-1551434678-e076c223a692?auto=format&fit=crop&w=400&q=80', 'alt' => 'Collaboration', 'width' => 'w-full', 'alignment' => 'center', 'border_radius' => 'rounded-xl']],
                            ['type' => 'spacer', 'data' => ['height' => 16]],
                            ['type' => 'heading', 'data' => ['text' => 'Team Collaboration', 'level' => 3, 'alignment' => 'center', 'font_weight' => 'font-bold', 'color' => '']],
                            ['type' => 'text', 'data' => ['content' => 'Work together seamlessly with shared workspaces and real-time editing.', 'font_size' => 'text-sm', 'alignment' => 'center', 'color' => 'var(--theme-body-muted-color)']],
                        ]],
                        ['blocks' => [
                            ['type' => 'image', 'data' => ['src' => 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=400&q=80', 'alt' => 'Security', 'width' => 'w-full', 'alignment' => 'center', 'border_radius' => 'rounded-xl']],
                            ['type' => 'spacer', 'data' => ['height' => 16]],
                            ['type' => 'heading', 'data' => ['text' => 'Enterprise Security', 'level' => 3, 'alignment' => 'center', 'font_weight' => 'font-bold', 'color' => '']],
                            ['type' => 'text', 'data' => ['content' => 'Bank-level encryption and compliance certifications for peace of mind.', 'font_size' => 'text-sm', 'alignment' => 'center', 'color' => 'var(--theme-body-muted-color)']],
                        ]],
                    ],
                ]),
            ]),
        ]);

        // 7. Icon Features Grid
        $this->manager->registerPart('core.features_icon_grid', [
            'name' => 'Icon Features Grid',
            'slug' => 'features-icon-grid',
            'description' => 'A grid of icon boxes showcasing features with customizable icons.',
            'category' => 'features',
            'content_raw' => $this->doc([
                $this->block('heading', ['text' => 'Why Choose Us', 'level' => 2, 'alignment' => 'center', 'font_weight' => 'font-bold', 'color' => '']),
                $this->block('spacer', ['height' => 8]),
                $this->block('text', ['content' => 'We provide the tools you need to succeed.', 'font_size' => 'text-lg', 'alignment' => 'center', 'color' => 'var(--theme-body-muted-color)']),
                $this->block('spacer', ['height' => 40]),
                $this->block('row', [
                    'columns' => 3, 'layout_preset' => 'thirds', 'column_widths' => ['1/3', '1/3', '1/3'],
                    'gap' => 'gap-6', 'vertical_align' => 'start',
                    'columns_data' => [
                        ['blocks' => [['type' => 'icon_box', 'data' => ['icon' => 'fas fa-rocket', 'title' => 'Fast Performance', 'description' => 'Optimized for speed and efficiency across all devices.', 'layout' => 'centered', 'icon_color' => '#ffffff', 'icon_bg' => '#4f46e5']]]],
                        ['blocks' => [['type' => 'icon_box', 'data' => ['icon' => 'fas fa-shield-alt', 'title' => 'Secure & Reliable', 'description' => 'Enterprise-grade security with 99.9% uptime guarantee.', 'layout' => 'centered', 'icon_color' => '#ffffff', 'icon_bg' => '#059669']]]],
                        ['blocks' => [['type' => 'icon_box', 'data' => ['icon' => 'fas fa-headset', 'title' => '24/7 Support', 'description' => 'Expert support team available around the clock.', 'layout' => 'centered', 'icon_color' => '#ffffff', 'icon_bg' => '#d97706']]]],
                    ],
                ]),
            ]),
        ]);

        // 8. Benefits Checklist (using what_you_get pattern block)
        $this->manager->registerPart('core.benefits_checklist', [
            'name' => 'Benefits Checklist',
            'slug' => 'benefits-checklist',
            'description' => 'A comprehensive benefits list with checkmarks and highlight box.',
            'category' => 'features',
            'content_raw' => $this->singleBlock('what_you_get', [
                'heading' => 'What\'s Included',
                'subheading' => 'Everything you need to launch and grow your online business',
                'button_text' => 'See All Features',
                'button_link' => '#',
                'features' => [
                    'Unlimited projects & workspaces', 'Real-time collaboration tools', 'Advanced analytics dashboard',
                    'Custom domain support', 'SSL certificate included', 'SEO optimization tools',
                    'Email marketing integration', 'Payment processing ready', '24/7 customer support',
                    'API access for developers', 'White-label options', 'Priority feature requests',
                ],
                'show_highlight' => true,
                'highlight_title' => '🚀 Free Migration Service',
                'highlight_text' => 'We\'ll migrate your existing data for free. No downtime, no hassle.',
                'banner_title' => 'Start building today — your first 30 days are free',
                'banner_text' => 'No credit card required. Cancel anytime.',
            ]),
        ]);

        // 9. Comparison Table
        $this->manager->registerPart('core.comparison_table', [
            'name' => 'Comparison Table',
            'slug' => 'comparison-table',
            'description' => 'A side-by-side comparison table for plans or products.',
            'category' => 'features',
            'content_raw' => $this->doc([
                $this->block('heading', ['text' => 'How We Compare', 'level' => 2, 'alignment' => 'center', 'font_weight' => 'font-bold', 'color' => '']),
                $this->block('spacer', ['height' => 8]),
                $this->block('text', ['content' => 'See why businesses choose us over the competition.', 'font_size' => 'text-base', 'alignment' => 'center', 'color' => 'var(--theme-body-muted-color)']),
                $this->block('spacer', ['height' => 32]),
                $this->block('html_block', ['html' => '<table style="width:100%;border-collapse:collapse;border-radius:12px;overflow:hidden;box-shadow:0 4px 16px rgba(0,0,0,0.06)"><thead><tr style="background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#fff"><th style="padding:16px 20px;text-align:left;font-weight:700">Feature</th><th style="padding:16px 20px;text-align:center;font-weight:700">Us</th><th style="padding:16px 20px;text-align:center;font-weight:700">Others</th></tr></thead><tbody><tr style="background:#fff;border-bottom:1px solid #f1f5f9"><td style="padding:14px 20px;font-weight:500">Unlimited Projects</td><td style="padding:14px 20px;text-align:center;color:#059669;font-weight:700">✓</td><td style="padding:14px 20px;text-align:center;color:#dc2626">✗</td></tr><tr style="background:#f8f9ff;border-bottom:1px solid #f1f5f9"><td style="padding:14px 20px;font-weight:500">24/7 Support</td><td style="padding:14px 20px;text-align:center;color:#059669;font-weight:700">✓</td><td style="padding:14px 20px;text-align:center;color:#d97706">Limited</td></tr><tr style="background:#fff;border-bottom:1px solid #f1f5f9"><td style="padding:14px 20px;font-weight:500">API Access</td><td style="padding:14px 20px;text-align:center;color:#059669;font-weight:700">✓</td><td style="padding:14px 20px;text-align:center;color:#dc2626">✗</td></tr><tr style="background:#f8f9ff"><td style="padding:14px 20px;font-weight:500">Custom Branding</td><td style="padding:14px 20px;text-align:center;color:#059669;font-weight:700">✓</td><td style="padding:14px 20px;text-align:center;color:#d97706">Paid Add-on</td></tr></tbody></table>', 'wrap_raw' => false]),
            ]),
        ]);
    }

    // ═══════════════════════════════════════════════
    //  Pricing (2 parts)
    // ═══════════════════════════════════════════════

    protected function seedPricing(): void
    {
        // 10. Pricing Cards
        $this->manager->registerPart('core.pricing_cards', [
            'name' => 'Pricing Cards',
            'slug' => 'pricing-cards',
            'description' => 'Three-tier pricing cards with features and CTA buttons.',
            'category' => 'pricing',
            'content_raw' => $this->singleBlock('pricing_matrix', [
                'style' => 'cards',
            ]),
        ]);

        // 11. Simple Pricing
        $this->manager->registerPart('core.pricing_simple', [
            'name' => 'Simple Pricing',
            'slug' => 'pricing-simple',
            'description' => 'A clean, minimal pricing layout built from atomic elements.',
            'category' => 'pricing',
            'content_raw' => $this->doc([
                $this->block('heading', ['text' => 'Simple, Transparent Pricing', 'level' => 2, 'alignment' => 'center', 'font_weight' => 'font-bold', 'color' => '']),
                $this->block('spacer', ['height' => 8]),
                $this->block('text', ['content' => 'No hidden fees. No surprises. Pick a plan that works for you.', 'font_size' => 'text-lg', 'alignment' => 'center', 'color' => 'var(--theme-body-muted-color)']),
                $this->block('spacer', ['height' => 40]),
                $this->block('row', [
                    'columns' => 3, 'layout_preset' => 'thirds', 'column_widths' => ['1/3', '1/3', '1/3'],
                    'gap' => 'gap-6', 'vertical_align' => 'start',
                    'columns_data' => [
                        ['blocks' => [
                            ['type' => 'heading', 'data' => ['text' => 'Starter', 'level' => 3, 'alignment' => 'center', 'font_weight' => 'font-bold', 'color' => '']],
                            ['type' => 'heading', 'data' => ['text' => '$9/mo', 'level' => 2, 'alignment' => 'center', 'font_weight' => 'font-extrabold', 'color' => '#4f46e5']],
                            ['type' => 'divider', 'data' => ['color' => 'gray-200', 'spacing' => 'py-4', 'width' => 'full', 'style' => 'solid']],
                            ['type' => 'text', 'data' => ['content' => '✓ 5 Projects\n✓ 10GB Storage\n✓ Email Support\n✓ Basic Analytics', 'font_size' => 'text-sm', 'alignment' => 'center', 'color' => 'var(--theme-body-muted-color)']],
                            ['type' => 'spacer', 'data' => ['height' => 20]],
                            ['type' => 'button', 'data' => ['label' => 'Choose Starter', 'url' => '#', 'style' => 'secondary', 'size' => 'px-6 py-3 text-base', 'alignment' => 'center']],
                        ]],
                        ['blocks' => [
                            ['type' => 'heading', 'data' => ['text' => 'Professional', 'level' => 3, 'alignment' => 'center', 'font_weight' => 'font-bold', 'color' => '']],
                            ['type' => 'heading', 'data' => ['text' => '$29/mo', 'level' => 2, 'alignment' => 'center', 'font_weight' => 'font-extrabold', 'color' => '#4f46e5']],
                            ['type' => 'divider', 'data' => ['color' => 'gray-200', 'spacing' => 'py-4', 'width' => 'full', 'style' => 'solid']],
                            ['type' => 'text', 'data' => ['content' => '✓ Unlimited Projects\n✓ 100GB Storage\n✓ Priority Support\n✓ Advanced Analytics\n✓ API Access', 'font_size' => 'text-sm', 'alignment' => 'center', 'color' => 'var(--theme-body-muted-color)']],
                            ['type' => 'spacer', 'data' => ['height' => 20]],
                            ['type' => 'button', 'data' => ['label' => 'Choose Pro', 'url' => '#', 'style' => 'primary', 'size' => 'px-6 py-3 text-base', 'alignment' => 'center']],
                        ]],
                        ['blocks' => [
                            ['type' => 'heading', 'data' => ['text' => 'Enterprise', 'level' => 3, 'alignment' => 'center', 'font_weight' => 'font-bold', 'color' => '']],
                            ['type' => 'heading', 'data' => ['text' => 'Custom', 'level' => 2, 'alignment' => 'center', 'font_weight' => 'font-extrabold', 'color' => '#4f46e5']],
                            ['type' => 'divider', 'data' => ['color' => 'gray-200', 'spacing' => 'py-4', 'width' => 'full', 'style' => 'solid']],
                            ['type' => 'text', 'data' => ['content' => '✓ Everything in Pro\n✓ Unlimited Storage\n✓ Dedicated Support\n✓ Custom Integrations\n✓ SLA Guarantee', 'font_size' => 'text-sm', 'alignment' => 'center', 'color' => 'var(--theme-body-muted-color)']],
                            ['type' => 'spacer', 'data' => ['height' => 20]],
                            ['type' => 'button', 'data' => ['label' => 'Contact Sales', 'url' => '#', 'style' => 'secondary', 'size' => 'px-6 py-3 text-base', 'alignment' => 'center']],
                        ]],
                    ],
                ]),
            ]),
        ]);
    }

    // ═══════════════════════════════════════════════
    //  Content (4 parts)
    // ═══════════════════════════════════════════════

    protected function seedContent(): void
    {
        // 12. FAQ Section
        $this->manager->registerPart('core.faq_section', [
            'name' => 'FAQ Section',
            'slug' => 'faq-section',
            'description' => 'A frequently asked questions section with accordion items.',
            'category' => 'content',
            'content_raw' => $this->doc([
                $this->block('heading', ['text' => 'Frequently Asked Questions', 'level' => 2, 'alignment' => 'center', 'font_weight' => 'font-bold', 'color' => '']),
                $this->block('spacer', ['height' => 8]),
                $this->block('text', ['content' => 'Find answers to the most common questions about our platform.', 'font_size' => 'text-base', 'alignment' => 'center', 'color' => 'var(--theme-body-muted-color)']),
                $this->block('spacer', ['height' => 32]),
                $this->block('accordion', [
                    'items' => [
                        ['title' => 'How do I get started?', 'content' => 'Simply sign up for a free account, choose your plan, and start building. Our setup wizard will guide you through the process in under 5 minutes.', 'open' => true],
                        ['title' => 'Can I cancel my subscription?', 'content' => 'Yes, you can cancel your subscription at any time from your account settings. There are no cancellation fees.', 'open' => false],
                        ['title' => 'Do you offer a free trial?', 'content' => 'Yes! We offer a 14-day free trial with full access to all features. No credit card required.', 'open' => false],
                        ['title' => 'What payment methods do you accept?', 'content' => 'We accept all major credit cards, PayPal, and bank transfers for annual plans.', 'open' => false],
                        ['title' => 'Is my data secure?', 'content' => 'Absolutely. We use bank-level encryption and are SOC 2 compliant. Your data is backed up daily.', 'open' => false],
                    ],
                    'style' => 'separate',
                ]),
            ]),
        ]);

        // 13. Content 2 Columns
        $this->manager->registerPart('core.content_2col', [
            'name' => 'Content 2 Columns',
            'slug' => 'content-2-columns',
            'description' => 'A two-column content layout for about sections or feature descriptions.',
            'category' => 'content',
            'content_raw' => $this->doc([
                $this->block('row', [
                    'columns' => 2, 'layout_preset' => 'halves', 'column_widths' => ['1/2', '1/2'],
                    'gap' => 'gap-12', 'vertical_align' => 'start',
                    'columns_data' => [
                        ['blocks' => [
                            ['type' => 'heading', 'data' => ['text' => 'Our Mission', 'level' => 2, 'alignment' => 'left', 'font_weight' => 'font-bold', 'color' => '']],
                            ['type' => 'spacer', 'data' => ['height' => 12]],
                            ['type' => 'text', 'data' => ['content' => 'We believe in empowering businesses with tools that are simple, powerful, and accessible. Our platform removes complexity so you can focus on what matters most — growing your business.', 'font_size' => 'text-base', 'alignment' => 'left', 'color' => 'var(--theme-body-muted-color)']],
                        ]],
                        ['blocks' => [
                            ['type' => 'heading', 'data' => ['text' => 'Our Vision', 'level' => 2, 'alignment' => 'left', 'font_weight' => 'font-bold', 'color' => '']],
                            ['type' => 'spacer', 'data' => ['height' => 12]],
                            ['type' => 'text', 'data' => ['content' => 'A world where every entrepreneur has access to enterprise-grade tools without the enterprise-grade complexity. We are building the future of business software, one feature at a time.', 'font_size' => 'text-base', 'alignment' => 'left', 'color' => 'var(--theme-body-muted-color)']],
                        ]],
                    ],
                ]),
            ]),
        ]);

        // 14. Text with Sidebar
        $this->manager->registerPart('core.text_with_sidebar', [
            'name' => 'Text with Sidebar',
            'slug' => 'text-with-sidebar',
            'description' => 'Main content area with a sidebar for navigation or highlights.',
            'category' => 'content',
            'content_raw' => $this->doc([
                $this->block('row', [
                    'columns' => 2, 'layout_preset' => 'sidebar-right', 'column_widths' => ['2/3', '1/3'],
                    'gap' => 'gap-10', 'vertical_align' => 'start',
                    'columns_data' => [
                        ['blocks' => [
                            ['type' => 'heading', 'data' => ['text' => 'Getting Started Guide', 'level' => 2, 'alignment' => 'left', 'font_weight' => 'font-bold', 'color' => '']],
                            ['type' => 'spacer', 'data' => ['height' => 12]],
                            ['type' => 'text', 'data' => ['content' => "Welcome to our platform! This guide will walk you through the essential steps to set up your account and start using all the features available to you.\n\nStep 1: Create your workspace and invite team members.\nStep 2: Connect your domain and configure DNS settings.\nStep 3: Customize your site using our visual builder.\nStep 4: Launch and start tracking your analytics.", 'font_size' => 'text-base', 'alignment' => 'left', 'color' => 'var(--theme-body-color)']],
                        ]],
                        ['blocks' => [
                            ['type' => 'heading', 'data' => ['text' => 'Quick Links', 'level' => 4, 'alignment' => 'left', 'font_weight' => 'font-bold', 'color' => '']],
                            ['type' => 'divider', 'data' => ['color' => 'gray-200', 'spacing' => 'py-2', 'width' => 'full', 'style' => 'solid']],
                            ['type' => 'text', 'data' => ['content' => "→ Documentation\n→ API Reference\n→ Video Tutorials\n→ Community Forum\n→ Support Center", 'font_size' => 'text-sm', 'alignment' => 'left', 'color' => '#4f46e5']],
                        ]],
                    ],
                ]),
            ]),
        ]);

        // 15. Blog Preview
        $this->manager->registerPart('core.blog_preview', [
            'name' => 'Blog Preview',
            'slug' => 'blog-preview',
            'description' => 'A three-column blog post preview section with images.',
            'category' => 'content',
            'content_raw' => $this->doc([
                $this->block('heading', ['text' => 'Latest from Our Blog', 'level' => 2, 'alignment' => 'center', 'font_weight' => 'font-bold', 'color' => '']),
                $this->block('spacer', ['height' => 8]),
                $this->block('text', ['content' => 'Stay updated with our latest insights and tips.', 'font_size' => 'text-base', 'alignment' => 'center', 'color' => 'var(--theme-body-muted-color)']),
                $this->block('spacer', ['height' => 32]),
                $this->block('row', [
                    'columns' => 3, 'layout_preset' => 'thirds', 'column_widths' => ['1/3', '1/3', '1/3'],
                    'gap' => 'gap-8', 'vertical_align' => 'start',
                    'columns_data' => [
                        ['blocks' => [
                            ['type' => 'image', 'data' => ['src' => 'https://images.unsplash.com/photo-1499750310107-5fef28a66643?auto=format&fit=crop&w=400&q=80', 'alt' => 'Blog post', 'width' => 'w-full', 'alignment' => 'center', 'border_radius' => 'rounded-xl']],
                            ['type' => 'spacer', 'data' => ['height' => 12]],
                            ['type' => 'heading', 'data' => ['text' => '10 Tips to Boost Your Productivity', 'level' => 4, 'alignment' => 'left', 'font_weight' => 'font-bold', 'color' => '']],
                            ['type' => 'text', 'data' => ['content' => 'Learn the habits and tools that top performers use to stay productive every day.', 'font_size' => 'text-sm', 'alignment' => 'left', 'color' => 'var(--theme-body-muted-color)']],
                        ]],
                        ['blocks' => [
                            ['type' => 'image', 'data' => ['src' => 'https://images.unsplash.com/photo-1432888622747-4eb9a8efeb07?auto=format&fit=crop&w=400&q=80', 'alt' => 'Blog post', 'width' => 'w-full', 'alignment' => 'center', 'border_radius' => 'rounded-xl']],
                            ['type' => 'spacer', 'data' => ['height' => 12]],
                            ['type' => 'heading', 'data' => ['text' => 'The Future of Remote Work', 'level' => 4, 'alignment' => 'left', 'font_weight' => 'font-bold', 'color' => '']],
                            ['type' => 'text', 'data' => ['content' => 'How companies are adapting to the new normal and what it means for your career.', 'font_size' => 'text-sm', 'alignment' => 'left', 'color' => 'var(--theme-body-muted-color)']],
                        ]],
                        ['blocks' => [
                            ['type' => 'image', 'data' => ['src' => 'https://images.unsplash.com/photo-1504384308090-c894fdcc538d?auto=format&fit=crop&w=400&q=80', 'alt' => 'Blog post', 'width' => 'w-full', 'alignment' => 'center', 'border_radius' => 'rounded-xl']],
                            ['type' => 'spacer', 'data' => ['height' => 12]],
                            ['type' => 'heading', 'data' => ['text' => 'Scaling Your Startup in 2025', 'level' => 4, 'alignment' => 'left', 'font_weight' => 'font-bold', 'color' => '']],
                            ['type' => 'text', 'data' => ['content' => 'Proven strategies from founders who scaled from zero to millions.', 'font_size' => 'text-sm', 'alignment' => 'left', 'color' => 'var(--theme-body-muted-color)']],
                        ]],
                    ],
                ]),
                $this->block('spacer', ['height' => 24]),
                $this->block('button', ['label' => 'View All Posts →', 'url' => '/blog', 'style' => 'secondary', 'size' => 'px-6 py-3 text-base', 'alignment' => 'center']),
            ]),
        ]);
    }

    // ═══════════════════════════════════════════════
    //  Social Proof (3 parts)
    // ═══════════════════════════════════════════════

    protected function seedSocialProof(): void
    {
        // 16. Testimonials
        $this->manager->registerPart('core.testimonials', [
            'name' => 'Testimonials',
            'slug' => 'testimonials',
            'description' => 'A three-column testimonial section with quotes and author info.',
            'category' => 'social-proof',
            'content_raw' => $this->doc([
                $this->block('heading', ['text' => 'What Our Customers Say', 'level' => 2, 'alignment' => 'center', 'font_weight' => 'font-bold', 'color' => '']),
                $this->block('spacer', ['height' => 8]),
                $this->block('text', ['content' => 'Trusted by thousands of businesses worldwide.', 'font_size' => 'text-lg', 'alignment' => 'center', 'color' => 'var(--theme-body-muted-color)']),
                $this->block('spacer', ['height' => 32]),
                $this->block('row', [
                    'columns' => 3, 'layout_preset' => 'thirds', 'column_widths' => ['1/3', '1/3', '1/3'],
                    'gap' => 'gap-6', 'vertical_align' => 'start',
                    'columns_data' => [
                        ['blocks' => [['type' => 'testimonial', 'data' => ['quote' => 'This platform completely transformed how we manage our projects. The analytics alone saved us 20 hours per week.', 'author_name' => 'Sarah Chen', 'author_role' => 'CTO, TechStart Inc.', 'avatar_url' => '', 'style' => 'card', 'rating' => 5, 'alignment' => 'left']]]],
                        ['blocks' => [['type' => 'testimonial', 'data' => ['quote' => 'Best investment we made this year. The ROI was visible within the first month of implementation.', 'author_name' => 'Michael Rodriguez', 'author_role' => 'Founder, GrowthLab', 'avatar_url' => '', 'style' => 'card', 'rating' => 5, 'alignment' => 'left']]]],
                        ['blocks' => [['type' => 'testimonial', 'data' => ['quote' => 'Incredible support team and a product that just works. We migrated from three different tools to this one.', 'author_name' => 'Emma Wilson', 'author_role' => 'Head of Ops, ScaleUp', 'avatar_url' => '', 'style' => 'card', 'rating' => 5, 'alignment' => 'left']]]],
                    ],
                ]),
            ]),
        ]);

        // 17. Stats Counter
        $this->manager->registerPart('core.stats_counter', [
            'name' => 'Stats Counter',
            'slug' => 'stats-counter',
            'description' => 'A row of impressive numbers and statistics.',
            'category' => 'social-proof',
            'content_raw' => $this->doc([
                $this->block('section', [
                    'bg_color' => 'var(--theme-surface-color)', 'bg_image' => '', 'padding' => 'py-16',
                    'blocks' => [
                        ['type' => 'row', 'data' => [
                            'columns' => 4, 'layout_preset' => 'quarters', 'column_widths' => ['1/4', '1/4', '1/4', '1/4'],
                            'gap' => 'gap-6', 'vertical_align' => 'center',
                            'columns_data' => [
                                ['blocks' => [['type' => 'counter', 'data' => ['value' => '10', 'label' => 'Years of Experience', 'suffix' => '+', 'icon' => 'fas fa-calendar', 'alignment' => 'center']]]],
                                ['blocks' => [['type' => 'counter', 'data' => ['value' => '5000', 'label' => 'Happy Customers', 'suffix' => '+', 'icon' => 'fas fa-users', 'alignment' => 'center']]]],
                                ['blocks' => [['type' => 'counter', 'data' => ['value' => '99.9', 'label' => 'Uptime', 'suffix' => '%', 'icon' => 'fas fa-server', 'alignment' => 'center']]]],
                                ['blocks' => [['type' => 'counter', 'data' => ['value' => '24', 'label' => 'Support Available', 'suffix' => '/7', 'icon' => 'fas fa-headset', 'alignment' => 'center']]]],
                            ],
                        ]],
                    ],
                ]),
            ]),
        ]);

        // 18. Logo Cloud
        $this->manager->registerPart('core.logo_cloud', [
            'name' => 'Logo Cloud',
            'slug' => 'logo-cloud',
            'description' => 'A row of partner or client logos.',
            'category' => 'social-proof',
            'content_raw' => $this->doc([
                $this->block('heading', ['text' => 'Trusted by Industry Leaders', 'level' => 3, 'alignment' => 'center', 'font_weight' => 'font-semibold', 'color' => 'var(--theme-body-muted-color)']),
                $this->block('spacer', ['height' => 24]),
                $this->block('row', [
                    'columns' => 4, 'layout_preset' => 'quarters', 'column_widths' => ['1/4', '1/4', '1/4', '1/4'],
                    'gap' => 'gap-8', 'vertical_align' => 'center',
                    'columns_data' => [
                        ['blocks' => [['type' => 'heading', 'data' => ['text' => '🏢 Acme Corp', 'level' => 4, 'alignment' => 'center', 'font_weight' => 'font-bold', 'color' => 'var(--theme-body-muted-color)']]]],
                        ['blocks' => [['type' => 'heading', 'data' => ['text' => '🚀 RocketFuel', 'level' => 4, 'alignment' => 'center', 'font_weight' => 'font-bold', 'color' => 'var(--theme-body-muted-color)']]]],
                        ['blocks' => [['type' => 'heading', 'data' => ['text' => '💡 InnovateLab', 'level' => 4, 'alignment' => 'center', 'font_weight' => 'font-bold', 'color' => 'var(--theme-body-muted-color)']]]],
                        ['blocks' => [['type' => 'heading', 'data' => ['text' => '⚡ SpeedScale', 'level' => 4, 'alignment' => 'center', 'font_weight' => 'font-bold', 'color' => 'var(--theme-body-muted-color)']]]],
                    ],
                ]),
            ]),
        ]);
    }

    // ═══════════════════════════════════════════════
    //  Contact (2 parts)
    // ═══════════════════════════════════════════════

    protected function seedContact(): void
    {
        // 19. Contact Form
        $this->manager->registerPart('core.contact_form', [
            'name' => 'Contact Form',
            'slug' => 'contact-form',
            'description' => 'A two-column contact section with info and form.',
            'category' => 'contact',
            'content_raw' => $this->doc([
                $this->block('heading', ['text' => 'Get in Touch', 'level' => 2, 'alignment' => 'center', 'font_weight' => 'font-bold', 'color' => '']),
                $this->block('spacer', ['height' => 8]),
                $this->block('text', ['content' => 'Have a question? We\'d love to hear from you.', 'font_size' => 'text-base', 'alignment' => 'center', 'color' => 'var(--theme-body-muted-color)']),
                $this->block('spacer', ['height' => 32]),
                $this->block('row', [
                    'columns' => 2, 'layout_preset' => 'halves', 'column_widths' => ['1/2', '1/2'],
                    'gap' => 'gap-12', 'vertical_align' => 'start',
                    'columns_data' => [
                        ['blocks' => [
                            ['type' => 'icon_box', 'data' => ['icon' => 'fas fa-envelope', 'title' => 'Email Us', 'description' => 'hello@example.com', 'layout' => 'inline', 'icon_color' => '#ffffff', 'icon_bg' => '#4f46e5']],
                            ['type' => 'spacer', 'data' => ['height' => 12]],
                            ['type' => 'icon_box', 'data' => ['icon' => 'fas fa-phone', 'title' => 'Call Us', 'description' => '+1 (555) 000-1234', 'layout' => 'inline', 'icon_color' => '#ffffff', 'icon_bg' => '#059669']],
                            ['type' => 'spacer', 'data' => ['height' => 12]],
                            ['type' => 'icon_box', 'data' => ['icon' => 'fas fa-map-marker-alt', 'title' => 'Visit Us', 'description' => '123 Business Ave, Suite 100, San Francisco, CA 94102', 'layout' => 'inline', 'icon_color' => '#ffffff', 'icon_bg' => '#d97706']],
                        ]],
                        ['blocks' => [
                            ['type' => 'html_block', 'data' => ['html' => '<form style="display:flex;flex-direction:column;gap:14px"><input type="text" placeholder="Your Name" style="padding:12px 16px;border:1px solid #d1d5db;border-radius:10px;font-size:15px;outline:none"><input type="email" placeholder="Your Email" style="padding:12px 16px;border:1px solid #d1d5db;border-radius:10px;font-size:15px;outline:none"><textarea placeholder="Your Message" rows="4" style="padding:12px 16px;border:1px solid #d1d5db;border-radius:10px;font-size:15px;outline:none;resize:vertical"></textarea><button type="submit" style="padding:14px;background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#fff;border:none;border-radius:10px;font-weight:700;font-size:15px;cursor:pointer">Send Message</button></form>', 'wrap_raw' => false]],
                        ]],
                    ],
                ]),
            ]),
        ]);

        // 20. Contact Info
        $this->manager->registerPart('core.contact_info', [
            'name' => 'Contact Info',
            'slug' => 'contact-info',
            'description' => 'A three-column contact information display with icons.',
            'category' => 'contact',
            'content_raw' => $this->doc([
                $this->block('row', [
                    'columns' => 3, 'layout_preset' => 'thirds', 'column_widths' => ['1/3', '1/3', '1/3'],
                    'gap' => 'gap-8', 'vertical_align' => 'start',
                    'columns_data' => [
                        ['blocks' => [['type' => 'icon_box', 'data' => ['icon' => 'fas fa-envelope', 'title' => 'Email', 'description' => 'hello@example.com\nsupport@example.com', 'layout' => 'centered', 'icon_color' => '#ffffff', 'icon_bg' => '#4f46e5']]]],
                        ['blocks' => [['type' => 'icon_box', 'data' => ['icon' => 'fas fa-phone-alt', 'title' => 'Phone', 'description' => '+1 (555) 000-1234\nMon-Fri 9am-6pm', 'layout' => 'centered', 'icon_color' => '#ffffff', 'icon_bg' => '#059669']]]],
                        ['blocks' => [['type' => 'icon_box', 'data' => ['icon' => 'fas fa-map-marker-alt', 'title' => 'Office', 'description' => '123 Business Avenue\nSan Francisco, CA 94102', 'layout' => 'centered', 'icon_color' => '#ffffff', 'icon_bg' => '#d97706']]]],
                    ],
                ]),
            ]),
        ]);
    }

    // ═══════════════════════════════════════════════
    //  Media (2 parts)
    // ═══════════════════════════════════════════════

    protected function seedMedia(): void
    {
        // 21. Video Showcase
        $this->manager->registerPart('core.video_showcase', [
            'name' => 'Video Showcase',
            'slug' => 'video-showcase',
            'description' => 'A centered video player with heading and description.',
            'category' => 'media',
            'content_raw' => $this->doc([
                $this->block('heading', ['text' => 'See How It Works', 'level' => 2, 'alignment' => 'center', 'font_weight' => 'font-bold', 'color' => '']),
                $this->block('spacer', ['height' => 8]),
                $this->block('text', ['content' => 'Watch a quick walkthrough of our platform features in action.', 'font_size' => 'text-base', 'alignment' => 'center', 'color' => 'var(--theme-body-muted-color)']),
                $this->block('spacer', ['height' => 28]),
                $this->block('video', [
                    'url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                    'preview_image' => 'https://images.unsplash.com/photo-1551434678-e076c223a692?auto=format&fit=crop&w=1000&q=80',
                    'aspect_ratio' => '16/9',
                    'caption' => 'Product walkthrough — 3 minutes',
                ]),
            ]),
        ]);

        // 22. Gallery Grid
        $this->manager->registerPart('core.gallery_grid', [
            'name' => 'Gallery Grid',
            'slug' => 'gallery-grid',
            'description' => 'A responsive image gallery with heading.',
            'category' => 'media',
            'content_raw' => $this->doc([
                $this->block('heading', ['text' => 'Gallery', 'level' => 2, 'alignment' => 'center', 'font_weight' => 'font-bold', 'color' => '']),
                $this->block('spacer', ['height' => 8]),
                $this->block('text', ['content' => 'A collection of our best work and moments.', 'font_size' => 'text-base', 'alignment' => 'center', 'color' => 'var(--theme-body-muted-color)']),
                $this->block('spacer', ['height' => 28]),
                $this->block('gallery', [
                    'images' => [
                        ['url' => 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=600&q=80', 'alt' => 'Team working'],
                        ['url' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=600&q=80', 'alt' => 'Dashboard'],
                        ['url' => 'https://images.unsplash.com/photo-1551434678-e076c223a692?auto=format&fit=crop&w=600&q=80', 'alt' => 'Office'],
                        ['url' => 'https://images.unsplash.com/photo-1504384308090-c894fdcc538d?auto=format&fit=crop&w=600&q=80', 'alt' => 'Conference'],
                        ['url' => 'https://images.unsplash.com/photo-1499750310107-5fef28a66643?auto=format&fit=crop&w=600&q=80', 'alt' => 'Workspace'],
                        ['url' => 'https://images.unsplash.com/photo-1432888622747-4eb9a8efeb07?auto=format&fit=crop&w=600&q=80', 'alt' => 'Nature'],
                    ],
                    'columns' => 3,
                    'gap' => 'gap-4',
                    'rounded' => 'rounded-xl',
                ]),
            ]),
        ]);
    }

    // ═══════════════════════════════════════════════
    //  E-Commerce (2 parts)
    // ═══════════════════════════════════════════════

    protected function seedEcommerce(): void
    {
        // 23. Product Features
        $this->manager->registerPart('core.product_features', [
            'name' => 'Product Features',
            'slug' => 'product-features',
            'description' => 'A two-column product feature showcase with image and details.',
            'category' => 'ecommerce',
            'content_raw' => $this->doc([
                $this->block('row', [
                    'columns' => 2, 'layout_preset' => 'halves', 'column_widths' => ['1/2', '1/2'],
                    'gap' => 'gap-12', 'vertical_align' => 'center',
                    'columns_data' => [
                        ['blocks' => [
                            ['type' => 'image', 'data' => ['src' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=800&q=80', 'alt' => 'Product screenshot', 'width' => 'w-full', 'alignment' => 'center', 'border_radius' => 'rounded-2xl']],
                        ]],
                        ['blocks' => [
                            ['type' => 'heading', 'data' => ['text' => 'Built for Modern Commerce', 'level' => 2, 'alignment' => 'left', 'font_weight' => 'font-bold', 'color' => '']],
                            ['type' => 'spacer', 'data' => ['height' => 12]],
                            ['type' => 'text', 'data' => ['content' => 'Everything you need to run your online store — from product management to payment processing.', 'font_size' => 'text-base', 'alignment' => 'left', 'color' => 'var(--theme-body-muted-color)']],
                            ['type' => 'spacer', 'data' => ['height' => 20]],
                            ['type' => 'icon_box', 'data' => ['icon' => 'fas fa-shopping-cart', 'title' => 'Smart Cart', 'description' => 'Intelligent cart with upsells and cross-sells.', 'layout' => 'inline', 'icon_color' => '#ffffff', 'icon_bg' => '#4f46e5']],
                            ['type' => 'spacer', 'data' => ['height' => 10]],
                            ['type' => 'icon_box', 'data' => ['icon' => 'fas fa-credit-card', 'title' => 'Secure Payments', 'description' => 'Accept all major cards with PCI compliance.', 'layout' => 'inline', 'icon_color' => '#ffffff', 'icon_bg' => '#059669']],
                            ['type' => 'spacer', 'data' => ['height' => 10]],
                            ['type' => 'icon_box', 'data' => ['icon' => 'fas fa-chart-line', 'title' => 'Sales Analytics', 'description' => 'Track revenue, orders, and growth trends.', 'layout' => 'inline', 'icon_color' => '#ffffff', 'icon_bg' => '#d97706']],
                            ['type' => 'spacer', 'data' => ['height' => 24]],
                            ['type' => 'button', 'data' => ['label' => 'Start Selling', 'url' => '#', 'style' => 'primary', 'size' => 'px-8 py-3 text-base', 'alignment' => 'left']],
                        ]],
                    ],
                ]),
            ]),
        ]);

        // 24. Product CTA
        $this->manager->registerPart('core.product_cta', [
            'name' => 'Product CTA',
            'slug' => 'product-cta',
            'description' => 'A bold product call-to-action with gradient background and dual buttons.',
            'category' => 'ecommerce',
            'content_raw' => $this->doc([
                $this->block('section', [
                    'bg_color' => '', 'bg_image' => '', 'padding' => 'py-20',
                    'style' => 'background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff; text-align: center;',
                    'blocks' => [
                        ['type' => 'heading', 'data' => ['text' => 'Ready to Launch Your Store?', 'level' => 2, 'alignment' => 'center', 'font_weight' => 'font-bold', 'color' => '#ffffff']],
                        ['type' => 'spacer', 'data' => ['height' => 12]],
                        ['type' => 'text', 'data' => ['content' => 'Join 10,000+ merchants selling on our platform. Start your free trial today.', 'font_size' => 'text-lg', 'alignment' => 'center', 'color' => '#94a3b8']],
                        ['type' => 'spacer', 'data' => ['height' => 28]],
                        ['type' => 'row', 'data' => [
                            'columns' => 2, 'layout_preset' => 'halves', 'column_widths' => ['1/2', '1/2'],
                            'gap' => 'gap-4', 'vertical_align' => 'center',
                            'columns_data' => [
                                ['blocks' => [['type' => 'button', 'data' => ['label' => 'Start Free Trial', 'url' => '#', 'style' => 'primary', 'size' => 'px-8 py-4 text-lg', 'alignment' => 'right']]]],
                                ['blocks' => [['type' => 'button', 'data' => ['label' => 'Schedule Demo', 'url' => '#', 'style' => 'secondary', 'size' => 'px-8 py-4 text-lg', 'alignment' => 'left', 'text_color' => '#ffffff', 'border_color' => '#475569', 'hover_bg_color' => 'rgba(255,255,255,0.05)']]]],
                            ],
                        ]],
                    ],
                ]),
            ]),
        ]);

        // 25. Service Showcase (Travel/Real Estate style)
        $this->manager->registerPart('core.service_showcase', [
            'name' => 'Service Showcase',
            'slug' => 'service-showcase',
            'description' => 'A visual service showcase with icon boxes and call-to-action.',
            'category' => 'ecommerce',
            'content_raw' => $this->doc([
                $this->block('heading', ['text' => 'Our Services', 'level' => 2, 'alignment' => 'center', 'font_weight' => 'font-bold', 'color' => '']),
                $this->block('spacer', ['height' => 8]),
                $this->block('text', ['content' => 'Comprehensive solutions tailored to your industry needs.', 'font_size' => 'text-lg', 'alignment' => 'center', 'color' => 'var(--theme-body-muted-color)']),
                $this->block('spacer', ['height' => 40]),
                $this->block('row', [
                    'columns' => 4, 'layout_preset' => 'quarters', 'column_widths' => ['1/4', '1/4', '1/4', '1/4'],
                    'gap' => 'gap-6', 'vertical_align' => 'start',
                    'columns_data' => [
                        ['blocks' => [['type' => 'icon_box', 'data' => ['icon' => 'fas fa-globe', 'title' => 'Web Design', 'description' => 'Beautiful, responsive websites that convert visitors into customers.', 'layout' => 'centered', 'icon_color' => '#ffffff', 'icon_bg' => '#4f46e5', 'link_url' => '#']]]],
                        ['blocks' => [['type' => 'icon_box', 'data' => ['icon' => 'fas fa-mobile-alt', 'title' => 'Mobile Apps', 'description' => 'Native and cross-platform apps for iOS and Android.', 'layout' => 'centered', 'icon_color' => '#ffffff', 'icon_bg' => '#7c3aed', 'link_url' => '#']]]],
                        ['blocks' => [['type' => 'icon_box', 'data' => ['icon' => 'fas fa-cloud', 'title' => 'Cloud Solutions', 'description' => 'Scalable cloud infrastructure and DevOps services.', 'layout' => 'centered', 'icon_color' => '#ffffff', 'icon_bg' => '#0891b2', 'link_url' => '#']]]],
                        ['blocks' => [['type' => 'icon_box', 'data' => ['icon' => 'fas fa-chart-bar', 'title' => 'Marketing', 'description' => 'Data-driven marketing strategies that drive results.', 'layout' => 'centered', 'icon_color' => '#ffffff', 'icon_bg' => '#059669', 'link_url' => '#']]]],
                    ],
                ]),
            ]),
        ]);
    }

    // ═══════════════════════════════════════════════
    //  Helpers
    // ═══════════════════════════════════════════════

    protected function doc(array $content): array
    {
        return ['type' => 'doc', 'content' => $content];
    }

    protected function block(string $type, array $data): array
    {
        return [
            'type' => 'landingBlock',
            'attrs' => [
                'id' => (string) Str::uuid(),
                'type' => $type,
                'data' => $data,
            ],
        ];
    }

    protected function singleBlock(string $type, array $data): array
    {
        return $this->doc([$this->block($type, $data)]);
    }
}
