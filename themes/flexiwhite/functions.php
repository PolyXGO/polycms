<?php

/**
 * ╔══════════════════════════════════════════════════════════════════╗
 * ║            POLYCMS — SAMPLE THEME functions.php                  ║
 * ╠══════════════════════════════════════════════════════════════════╣
 * ║  This file is auto-loaded when this theme is the active theme.   ║
 * ║  Use it to register hooks, filters, widget areas, and helpers.   ║
 * ║                                                                  ║
 * ║  IMPORTANT: This file runs on EVERY request when the theme is    ║
 * ║  active. Keep it performant — avoid heavy DB queries here.       ║
 * ║                                                                  ║
 * ║  LOAD ORDER:                                                     ║
 * ║  1. Laravel boots → ServiceProviders register                    ║
 * ║  2. ThemeServiceProvider loads active theme's functions.php       ║
 * ║  3. Modules' ServiceProviders boot (hooks registered)            ║
 * ║  4. Request handled → hooks fire as needed                       ║
 * ╚══════════════════════════════════════════════════════════════════╝
 */

use App\Facades\Hook;
use App\Models\WidgetArea;
use App\Services\SettingsService;
use App\Services\WidgetManager;
use Illuminate\Support\Str;

// ╔══════════════════════════════════════════════════════════════════╗
// ║ SECTION 1: THEME ACTIVATION HOOK                                ║
// ╚══════════════════════════════════════════════════════════════════╝

/**
 * Action: theme.activated
 * Fired in: ThemeServiceProvider when a theme is set as active
 *
 * Use this to run one-time setup tasks when the theme is first activated:
 * - Create default pages (Home, About, Contact)
 * - Set default settings (colors, typography)
 * - Create default menu structure
 *
 * @param object $theme  The theme object with slug, name, etc.
 */
Hook::addAction('theme.activated', function ($theme) {
    if ($theme->slug !== 'flexiwhite') {
        return;
    }

    $settings = app(\App\Services\SettingsService::class);

    // Only run once — skip if already initialized
    if ($settings->get('flexiwhite_initialized')) {
        \Log::info('[SampleTheme] Already initialized, skipping.');
        return;
    }

    \Log::info('[FlexiWhite] Initializing landing page...');

    // Build homepage content using landing block markers
    $contentRaw = [
        'type' => 'doc',
        'content' => [
            [
                'type' => 'landingBlock',
                'attrs' => [
                    'id' => \Illuminate\Support\Str::uuid()->toString(),
                    'type' => 'fw_hero_section',
                    'data' => [
                        'heading' => 'Build something amazing,<br>ship it faster.',
                        'subheading' => 'A modern, lightweight content management system built for performance, flexibility, and developer happiness.',
                        'button_text' => 'Explore the Blog',
                        'button_link' => '/posts',
                        'secondary_button_text' => 'Browse Products',
                        'secondary_button_url' => '/products',
                    ]
                ]
            ],
            [
                'type' => 'landingBlock',
                'attrs' => [
                    'id' => \Illuminate\Support\Str::uuid()->toString(),
                    'type' => 'fw_features_grid',
                    'data' => [
                        'heading' => 'Why Choose PolyCMS',
                        'subheading' => 'Everything you need to build, manage, and scale your content.',
                        'columns' => 3,
                        'features' => [
                            [
                                'icon' => 'BoltIcon',
                                'title' => 'Blazing Fast',
                                'description' => 'Optimized architecture with smart caching delivers sub-second page loads, ensuring the best experience for your visitors.'
                            ],
                            [
                                'icon' => 'ShieldCheckIcon',
                                'title' => 'Secure by Default',
                                'description' => 'Enterprise-grade security with CSRF protection, input sanitization, and role-based access control built into every layer.'
                            ],
                            [
                                'icon' => 'Squares2x2Icon',
                                'title' => 'Modular Design',
                                'description' => 'Extend functionality with themes and modules. Install, activate, and customize — no core modifications needed.'
                            ],
                            [
                                'icon' => 'GlobeAltIcon',
                                'title' => 'Multi-Language Ready',
                                'description' => 'Full internationalization support with RTL layouts, translation management, and locale-aware content delivery.'
                            ],
                            [
                                'icon' => 'ChartBarIcon',
                                'title' => 'SEO Optimized',
                                'description' => 'Clean semantic markup, structured data, customizable meta tags, and auto-generated sitemaps to maximize your search visibility.'
                            ],
                            [
                                'icon' => 'CodeBracketIcon',
                                'title' => 'Developer Friendly',
                                'description' => 'Built on Laravel with Blade templates, a powerful Hook system, REST API, and comprehensive documentation for rapid development.'
                            ]
                        ]
                    ]
                ]
            ],
            [
                'type' => 'landingBlock',
                'attrs' => [
                    'id' => \Illuminate\Support\Str::uuid()->toString(),
                    'type' => 'fw_stats_bar',
                    'data' => [
                        'stats' => [
                            ['value' => '99.9%', 'label' => 'Uptime'],
                            ['value' => '<200ms', 'label' => 'Response Time'],
                            ['value' => '50+', 'label' => 'Hooks & Filters'],
                            ['value' => '100%', 'label' => 'Open Source']
                        ]
                    ]
                ]
            ],
            [
                'type' => 'landingBlock',
                'attrs' => [
                    'id' => \Illuminate\Support\Str::uuid()->toString(),
                    'type' => 'fw_latest_posts',
                    'data' => [
                        'heading' => 'Latest Updates',
                        'count' => 6,
                        'columns' => 3,
                        'show_view_all' => true
                    ]
                ]
            ],
            [
                'type' => 'landingBlock',
                'attrs' => [
                    'id' => \Illuminate\Support\Str::uuid()->toString(),
                    'type' => 'fw_cta_section',
                    'data' => [
                        'heading' => 'Ready to get started?',
                        'text' => 'Set up your site in minutes. No credit card required.',
                        'button_text' => 'Get Started Free',
                        'button_url' => '/login'
                    ]
                ]
            ]
        ]
    ];

    $contentHtml = '';
    foreach ($contentRaw['content'] as $block) {
        $type = $block['attrs']['type'];
        $data = json_encode($block['attrs']['data'], JSON_HEX_APOS | JSON_HEX_QUOT);
        $contentHtml .= '<div data-type="landing-block" data-block-type="' . $type . '" data-block-data=\'' . $data . '\'></div>' . "\n";
    }

    try {
        // Create the landing page
        $page = \App\Models\Post::create([
            'user_id'      => 1,
            'title'        => 'Home',
            'slug'         => 'home',
            'type'         => 'page',
            'status'       => 'published',
            'content_html' => $contentHtml,
            'content_raw'  => $contentRaw,
            'published_at' => now(),
        ]);

        // Set as static homepage
        $settings->set('reading_show_on_front', 'page', 'reading');
        $settings->set('reading_page_on_front', (string) $page->id, 'reading');

        // Mark as initialized
        $settings->set('flexiwhite_initialized', 'yes', 'theme_options');

        \Log::info('[SampleTheme] Landing page created (ID: ' . $page->id . ') and set as static homepage.');
    } catch (\Exception $e) {
        \Log::error('[SampleTheme] Failed to create landing page: ' . $e->getMessage());
    }
}, 10);

// ╔══════════════════════════════════════════════════════════════════╗
// ║ SECTION 2: THEME VIEW DATA INJECTION                            ║
// ╚══════════════════════════════════════════════════════════════════╝

/**
 * Filter: theme.view.data
 * Fired in: ALL frontend controllers (PostController, PageController, etc.)
 *
 * This is the PRIMARY way themes receive data from the core.
 * Your Blade templates access these as $variables.
 *
 * @param array  $data      View data array (already contains 'post', 'page', etc.)
 * @param string $viewName  Which view is rendering ('home', 'posts.show', etc.)
 * @return array            Modified data array
 *
 * View names:
 * ┌──────────────────┬──────────────────────────────────┐
 * │ View Name        │ When Fired                       │
 * ├──────────────────┼──────────────────────────────────┤
 * │ home             │ Homepage                         │
 * │ posts.index      │ Blog listing / archive           │
 * │ posts.show       │ Single post                      │
 * │ pages.show       │ Single page                      │
 * │ products.index   │ Product listing                  │
 * │ products.show    │ Single product                   │
 * │ categories.show  │ Category archive                 │
 * │ tags.show        │ Tag archive                      │
 * │ authors.show     │ Author archive                   │
 * └──────────────────┴──────────────────────────────────┘
 */
Hook::addFilter('theme.view.data', function ($data, $viewName) {
    /** @var SettingsService $settingsService */
    $settingsService = app(SettingsService::class);

    // ── Global data (available in ALL views) ──────────────────
    $data['theme_name']    = 'FlexiWhite';
    $data['theme_version'] = '2.0.0';
    $data['site_title']    = $settingsService->get('site_title', 'PolyCMS');
    $data['tagline']       = $settingsService->get('tagline', 'Just another PolyCMS site');
    $data['current_year']  = date('Y');

    // ── View-specific data ────────────────────────────────────
    // Example: Pass recent posts to homepage
    // if ($viewName === 'home') {
    //     $data['recent_posts'] = \App\Models\Post::published()
    //         ->latest()
    //         ->take(6)
    //         ->get();
    // }

    return $data;
}, 10, 2);

// ╔══════════════════════════════════════════════════════════════════╗
// ║ SECTION 3: WIDGET AREA REGISTRATION                             ║
// ╚══════════════════════════════════════════════════════════════════╝

/**
 * Action: widgets.register_areas
 * Fired in: AppServiceProvider during bootstrap
 *
 * Widget areas are named regions in your theme where users can
 * drag & drop widgets via the admin panel (Appearance → Widgets).
 *
 * Convention:
 * - Use descriptive keys: homepage_hero, sidebar_blog, footer_col_1
 * - Group by page section with order ranges:
 *   • 10-99:  Homepage sections
 *   • 100-199: Sidebars
 *   • 200-299: Footer areas
 *   • 300+:    Custom areas
 *
 * @param WidgetManager $widgets  The widget manager service
 */
Hook::addAction('widgets.register_areas', function (WidgetManager $widgets): void {

    // ── Homepage Sections (order 10-69) ───────────────────────
    $widgets->registerArea('homepage_hero', [
        'name'        => 'Homepage Hero',
        'description' => 'Full-width hero banner displayed at the top of the homepage.',
        'order'       => 10,
    ]);

    $widgets->registerArea('homepage_intro', [
        'name'        => 'Company Introduction',
        'description' => 'Introductory text or media that describes the company.',
        'order'       => 20,
    ]);

    $widgets->registerArea('homepage_highlights', [
        'name'        => 'Key Highlights',
        'description' => 'Grid or columns showcasing company highlights or services.',
        'order'       => 30,
    ]);

    $widgets->registerArea('homepage_showcase', [
        'name'        => 'Showcase / Portfolio',
        'description' => 'Showcase projects, partners, or featured content.',
        'order'       => 40,
    ]);

    $widgets->registerArea('homepage_testimonials', [
        'name'        => 'Testimonials',
        'description' => 'Testimonials or social proof section.',
        'order'       => 50,
    ]);

    $widgets->registerArea('homepage_cta', [
        'name'        => 'Call to Action',
        'description' => 'Bottom call-to-action banner on the homepage.',
        'order'       => 60,
    ]);

    // ── Sidebars (order 100-199) ──────────────────────────────
    $widgets->registerArea('sidebar_primary', [
        'name'        => 'Primary Sidebar',
        'description' => 'Default sidebar for pages.',
        'order'       => 110,
    ]);

    $widgets->registerArea('sidebar_blog', [
        'name'        => 'Blog Sidebar',
        'description' => 'Sidebar for blog posts and listings.',
        'order'       => 120,
    ]);

    $widgets->registerArea('sidebar_shop', [
        'name'        => 'Shop Sidebar',
        'description' => 'Sidebar for product listings and product detail pages.',
        'order'       => 130,
    ]);

    // ── Footer Columns (order 200-299) ────────────────────────
    $widgets->registerArea('footer_col_1', [
        'name'        => 'Footer Column 1',
        'description' => 'First column in the footer (typically logo + about).',
        'order'       => 210,
    ]);

    $widgets->registerArea('footer_col_2', [
        'name'        => 'Footer Column 2',
        'description' => 'Second column in the footer (typically quick links).',
        'order'       => 220,
    ]);

    $widgets->registerArea('footer_col_3', [
        'name'        => 'Footer Column 3',
        'description' => 'Third column in the footer (typically contact info).',
        'order'       => 230,
    ]);
});

// ╔══════════════════════════════════════════════════════════════════╗
// ║ SECTION 4: THEME OPTIONS FILTER                                 ║
// ╚══════════════════════════════════════════════════════════════════╝

/**
 * Filter: theme.options.resolved
 * Fired in: theme_get_options() helper function
 *
 * Modify or extend theme options after they are resolved from the database.
 * Use this to compute derived values, apply business logic, or inject
 * environment-specific overrides.
 *
 * @param array $options        Resolved options from settings DB
 * @param array $groupSettings  Raw group settings definitions
 * @return array                Modified options
 */
// DEMO: Uncomment to add computed theme options
// Hook::addFilter('theme.options.resolved', function (array $options, array $groupSettings): array {
//     // Example: Compute a CSS class based on theme color
//     $primary = $options['primary_color'] ?? '#4f46e5';
//     $options['primary_color_rgb'] = implode(',', sscanf($primary, '#%02x%02x%02x'));
//     return $options;
// }, 10, 2);

/**
 * Register Footer Theme Options
 *
 * These settings appear in Admin > Appearance > Theme Options
 * and control footer copyright and attribution display.
 */
Hook::addFilter('settings.defaults', function ($defaults) {
    $defaults['theme_options']['flexiwhite_dark_mode_toggle'] = [
        'key'           => 'flexiwhite_dark_mode_toggle',
        'label'         => _l('Show Dark/Light Mode Toggle'),
        'description'   => _l('Enable or disable the theme toggle button on the menu.'),
        'type'          => 'toggle',
        'default'       => true,
        'group'         => 'theme_options',
        'section'       => 'flexiwhite',
        'section_label' => _l('FlexiWhite Theme Settings'),
        'section_description' => _l('Settings for the FlexiWhite theme. These options only apply when FlexiWhite is active as the Main Theme or a Sub Theme.'),
        'category'      => 'flexiwhite_header',
        'category_label'=> _l('Header Settings'),
        'category_description' => _l('Configure the site header: navigation alignment, dark/light mode toggle visibility, and default color scheme for new visitors.'),
        'section_order' => 10,
    ];

    $defaults['theme_options']['flexiwhite_default_color_mode'] = [
        'key'           => 'flexiwhite_default_color_mode',
        'label'         => _l('Default Color Mode'),
        'description'   => _l('Select the default color mode for new visitors. Users can still toggle it if the toggle button is enabled.'),
        'type'          => 'select',
        'default'       => 'light',
        'options'       => [
            ['value' => 'light', 'label' => _l('Light Mode')],
            ['value' => 'dark', 'label' => _l('Dark Mode')],
        ],
        'group'         => 'theme_options',
        'section'       => 'flexiwhite',
        'section_label' => _l('FlexiWhite Theme Settings'),
        'category'      => 'flexiwhite_header',
        'category_label'=> _l('Header Settings'),
        'section_order' => 10,
    ];

    $defaults['theme_options']['flexiwhite_header_menu_align'] = [
        'key'           => 'flexiwhite_header_menu_align',
        'label'         => _l('Header Menu Alignment'),
        'description'   => _l('Align the main navigation menu.'),
        'type'          => 'select',
        'options'       => ['left' => _l('Left'), 'center' => _l('Center'), 'right' => _l('Right'), 'justify' => _l('Justify (Equal Spacing)')],
        'default'       => 'right',
        'group'         => 'theme_options',
        'section'       => 'flexiwhite',
        'section_label' => _l('FlexiWhite Theme Settings'),
        'category'      => 'flexiwhite_header',
        'category_label'=> _l('Header Settings'),
        'section_order' => 10,
    ];

    $defaults['theme_options']['flexiwhite_footer_copyright'] = [
        'key'           => 'flexiwhite_footer_copyright',
        'label'         => _l('Footer Copyright Text'),
        'description'   => _l('Custom copyright text for the footer. Leave empty for default. Supports HTML.'),
        'type'          => 'string',
        'default'       => '',
        'group'         => 'theme_options',
        'section'       => 'flexiwhite',
        'section_label' => _l('FlexiWhite Theme Settings'),
        'category'      => 'flexiwhite_footer',
        'category_label'=> _l('Footer Settings'),
        'section_order' => 80,
    ];

    $defaults['theme_options']['flexiwhite_footer_powered_by'] = [
        'key'           => 'flexiwhite_footer_powered_by',
        'label'         => _l('Show "Powered by PolyCMS"'),
        'description'   => _l('Display "Powered by PolyCMS" attribution in the footer.'),
        'type'          => 'select',
        'options'       => [
            'show' => _l('Show'),
            'hide' => _l('Hide'),
        ],
        'default'       => 'show',
        'group'         => 'theme_options',
        'section'       => 'flexiwhite',
        'section_label' => _l('FlexiWhite Theme Settings'),
        'section_description' => _l('Settings for the FlexiWhite theme. These options only apply when FlexiWhite is active as the Main Theme or a Sub Theme.'),
        'category'      => 'flexiwhite_footer',
        'category_label'=> _l('Footer Settings'),
        'category_description' => _l('Customize the site footer: copyright text and "Powered by" attribution display.'),
        'section_order' => 80,
    ];

    // --- Listing Layout Options ---
    $defaults['theme_options']['flexiwhite_posts_columns'] = [
        'key'           => 'flexiwhite_posts_columns',
        'label'         => _l('Blog Grid Columns'),
        'description'   => _l('Number of columns for blog listing grid view.'),
        'type'          => 'select',
        'options'       => ['2' => '2', '3' => '3', '4' => '4'],
        'default'       => '3',
        'group'         => 'theme_options',
        'section'       => 'flexiwhite',
        'section_label' => _l('FlexiWhite Theme Settings'),
        'section_description' => _l('Settings for the FlexiWhite theme. These options only apply when FlexiWhite is active as the Main Theme or a Sub Theme.'),
        'category'      => 'flexiwhite_listing',
        'category_label'=> _l('Listing Layout'),
        'category_description' => _l('Control how blog posts and product listings appear: grid columns, default view mode (grid vs list), and card image placement.'),
        'section_order' => 70,
    ];

    $defaults['theme_options']['flexiwhite_posts_default_view'] = [
        'key'           => 'flexiwhite_posts_default_view',
        'label'         => _l('Blog Default View'),
        'type'          => 'select',
        'options'       => ['grid' => _l('Grid'), 'list' => _l('List')],
        'default'       => 'grid',
        'group'         => 'theme_options',
        'section'       => 'flexiwhite',
        'section_label' => _l('FlexiWhite Theme Settings'),
        'section_description' => _l('Settings for the FlexiWhite theme. These options only apply when FlexiWhite is active as the Main Theme or a Sub Theme.'),
        'category'      => 'flexiwhite_listing',
        'category_label'=> _l('Listing Layout'),
        'category_description' => _l('Control how blog posts and product listings appear: grid columns, default view mode (grid vs list), and card image placement.'),
        'section_order' => 70,
    ];

    $defaults['theme_options']['flexiwhite_posts_card_style'] = [
        'key'           => 'flexiwhite_posts_card_style',
        'label'         => _l('Blog Card Style'),
        'description'   => _l('Show image above or below title in grid cards.'),
        'type'          => 'select',
        'options'       => ['image_first' => _l('Image → Title'), 'title_first' => _l('Title → Image')],
        'default'       => 'image_first',
        'group'         => 'theme_options',
        'section'       => 'flexiwhite',
        'section_label' => _l('FlexiWhite Theme Settings'),
        'section_description' => _l('Settings for the FlexiWhite theme. These options only apply when FlexiWhite is active as the Main Theme or a Sub Theme.'),
        'category'      => 'flexiwhite_listing',
        'category_label'=> _l('Listing Layout'),
        'category_description' => _l('Control how blog posts and product listings appear: grid columns, default view mode (grid vs list), and card image placement.'),
        'section_order' => 70,
    ];

    $defaults['theme_options']['flexiwhite_products_columns'] = [
        'key'           => 'flexiwhite_products_columns',
        'label'         => _l('Products Grid Columns'),
        'type'          => 'select',
        'options'       => ['2' => '2', '3' => '3', '4' => '4'],
        'default'       => '3',
        'group'         => 'theme_options',
        'section'       => 'flexiwhite',
        'section_label' => _l('FlexiWhite Theme Settings'),
        'section_description' => _l('Settings for the FlexiWhite theme. These options only apply when FlexiWhite is active as the Main Theme or a Sub Theme.'),
        'category'      => 'flexiwhite_listing',
        'category_label'=> _l('Listing Layout'),
        'category_description' => _l('Control how blog posts and product listings appear: grid columns, default view mode (grid vs list), and card image placement.'),
        'section_order' => 70,
    ];

    $defaults['theme_options']['flexiwhite_products_default_view'] = [
        'key'           => 'flexiwhite_products_default_view',
        'label'         => _l('Products Default View'),
        'type'          => 'select',
        'options'       => ['grid' => _l('Grid'), 'list' => _l('List')],
        'default'       => 'grid',
        'group'         => 'theme_options',
        'section'       => 'flexiwhite',
        'section_label' => _l('FlexiWhite Theme Settings'),
        'section_description' => _l('Settings for the FlexiWhite theme. These options only apply when FlexiWhite is active as the Main Theme or a Sub Theme.'),
        'category'      => 'flexiwhite_listing',
        'category_label'=> _l('Listing Layout'),
        'category_description' => _l('Control how blog posts and product listings appear: grid columns, default view mode (grid vs list), and card image placement.'),
        'section_order' => 70,
    ];

    return $defaults;
});

// ╔══════════════════════════════════════════════════════════════════╗
// ║ SECTION 5: TEMPLATE CUSTOMIZATION                               ║
// ╚══════════════════════════════════════════════════════════════════╝

/**
 * Filter: theme.template.registry
 * Fired in: TemplateResolver when listing available templates
 *
 * Register custom page/post templates that users can select in the editor.
 * Each template maps to a Blade view file in your theme's resources/views/.
 *
 * @param array  $templates  Current template list
 * @param string $viewType   'page', 'post', or 'product'
 * @return array             Modified template list
 */
// DEMO: Register a custom page template
// Hook::addFilter('theme.template.registry', function (array $templates, string $viewType): array {
//     if ($viewType === 'page') {
//         $templates['full-width'] = [
//             'name' => 'Full Width (No Sidebar)',
//             'view' => 'pages.full-width',
//         ];
//         $templates['landing'] = [
//             'name' => 'Landing Page',
//             'view' => 'pages.landing',
//         ];
//     }
//     return $templates;
// }, 10, 2);

/**
 * Filter: theme.template.resolve
 * Fired in: TemplateResolver when determining which view to render
 *
 * Override template resolution for specific entities.
 *
 * @param string $viewName       The resolved view name
 * @param object $templateTheme  The theme being used
 * @param string $entityType     'page', 'post', 'product'
 * @param mixed  $entity         The entity being rendered
 * @return string                Modified view name
 */
// DEMO: Force a specific template for certain pages
// Hook::addFilter('theme.template.resolve', function ($viewName, $templateTheme, $entityType, $entity) {
//     if ($entityType === 'page' && $entity && $entity->slug === 'about') {
//         return 'pages.about'; // Use a custom about page template
//     }
//     return $viewName;
// }, 10, 4);

// ╔══════════════════════════════════════════════════════════════════╗
// ║ SECTION 6: SEO INTEGRATION                                      ║
// ╚══════════════════════════════════════════════════════════════════╝

/**
 * Filter: seo.canonical_url
 * Fired in: AppServiceProvider when generating <link rel="canonical">
 *
 * Modify the canonical URL. Common use cases:
 * - Strip query parameters from pagination pages
 * - Force HTTPS
 * - Handle www vs non-www
 *
 * @param string $url  The canonical URL
 * @return string      Modified URL
 */
// DEMO: Strip pagination query params from canonical
// Hook::addFilter('seo.canonical_url', function (string $url): string {
//     return preg_replace('/[?&]page=\d+/', '', $url);
// }, 10);

/**
 * Filter: seo.site_favicon
 * Fired in: AppServiceProvider when generating <link rel="icon">
 *
 * Override the favicon URL (e.g., use a theme-specific favicon).
 *
 * @param string|null $iconUrl  Current favicon URL
 * @return string|null          Modified favicon URL
 */
// DEMO: Use theme-specific favicon
// Hook::addFilter('seo.site_favicon', function (?string $iconUrl): ?string {
//     return $iconUrl ?: asset('themes/flexiwhite/favicon.ico');
// }, 10);

// ╔══════════════════════════════════════════════════════════════════╗
// ║ SECTION 7: CONTENT RENDERING                                    ║
// ╚══════════════════════════════════════════════════════════════════╝

/**
 * Filter: content.render.html
 * Fired in: ContentRenderer after all blocks are rendered to HTML
 *
 * Modify the final rendered HTML of block content.
 * Use cases: add rel="nofollow" to external links, lazy-load images, etc.
 *
 * @param string $html    The rendered HTML
 * @param array  $blocks  The source block array
 * @return string         Modified HTML
 */
// DEMO: Add loading="lazy" to all images in rendered content
// Hook::addFilter('content.render.html', function (string $html, array $blocks): string {
//     return str_replace('<img ', '<img loading="lazy" ', $html);
// }, 20, 2);

// ╔══════════════════════════════════════════════════════════════════╗
// ║ SECTION 8: LAYOUT ASSETS                                        ║
// ╚══════════════════════════════════════════════════════════════════╝

/**
 * Action: layout.register_assets
 * Fired in: AppServiceProvider during bootstrap
 *
 * Register CSS/JS files or inline snippets that will be included
 * in the theme's layout. This is how themes load their own assets.
 *
 * @param LayoutAssetManager $assetManager
 */
// DEMO: Theme-specific assets are typically loaded in the Blade layout.
// However, this hook is useful for conditional assets:
// Hook::addAction('layout.register_assets', function ($assetManager) {
//     $assetManager->addStylesheet('flexiwhite-main', asset('themes/flexiwhite/css/style.css'));
//     $assetManager->addScript('flexiwhite-main', asset('themes/flexiwhite/js/main.js'));
// }, 10);

// ╔══════════════════════════════════════════════════════════════════╗
// ║ SECTION 9: URL CUSTOMIZATION                                    ║
// ╚══════════════════════════════════════════════════════════════════╝

/**
 * Filter: post.frontend_url
 * Fired in: Post model when generating the frontend URL
 *
 * Customize how post URLs are generated.
 *
 * @param string $url   The generated URL path
 * @param Post   $post  The post model
 * @return string       Modified URL
 */
// DEMO: Add category prefix to post URLs
// Hook::addFilter('post.frontend_url', function (string $url, $post): string {
//     if ($post->categories->isNotEmpty()) {
//         $category = $post->categories->first();
//         return "/{$category->slug}/{$post->slug}";
//     }
//     return $url;
// }, 10, 2);

/**
 * Filter: category.frontend_url
 * Fired in: Category model when generating the frontend URL
 *
 * @param string   $url       The generated URL
 * @param Category $category  The category model
 * @return string             Modified URL
 */
// DEMO: Custom category URL structure
// Hook::addFilter('category.frontend_url', function (string $url, $category): string {
//     return $url; // Return as-is or modify
// }, 10, 2);

// ╔══════════════════════════════════════════════════════════════════╗
// ║ SECTION 10: TOPBAR MENU INTEGRATION                             ║
// ╚══════════════════════════════════════════════════════════════════╝

/**
 * Filter: topbar.menu.items
 * Fired in: TopbarMenuService when building the frontend topbar
 *
 * Add items to the frontend topbar (if your theme uses one).
 *
 * @param array   $items    Current menu items
 * @param Request $request  The current request
 * @param User    $user     The authenticated user (nullable)
 * @return array            Modified items
 */
// DEMO: Add a "Shop" link to the topbar
// Hook::addFilter('topbar.menu.items', function (array $items, $request, $user): array {
//     $items[] = [
//         'key'   => 'shop',
//         'label' => 'Shop',
//         'url'   => '/products',
//         'order' => 20,
//     ];
//     return $items;
// }, 10, 3);

// ╔══════════════════════════════════════════════════════════════════╗
// ║ SECTION 11: HELPER FUNCTIONS                                    ║
// ╚══════════════════════════════════════════════════════════════════╝

/*
 * Helper functions are available in ALL Blade templates when this theme
 * is active. Use function_exists() guards to prevent redeclaration errors
 * when themes are switched.
 */

/**
 * Get a theme modification (setting) value.
 *
 * Usage in Blade: {{ get_theme_mod('primary_color', '#4f46e5') }}
 *
 * This reads from the `settings` table with the key pattern:
 * theme_{theme_slug}_{key}
 */
if (!function_exists('get_theme_mod')) {
    function get_theme_mod($key, $default = null)
    {
        $manager = app(\App\Services\ThemeManager::class);
        if (method_exists($manager, 'getActiveMainTheme')) {
            $active = $manager->getActiveMainTheme();
            if ($active) {
                return app(SettingsService::class)->get("theme_{$active->slug}_{$key}", $default);
            }
        }
        return app(SettingsService::class)->get("theme_{$key}", $default);
    }
}

/**
 * Check if a widget area has any widgets assigned.
 *
 * Usage in Blade:
 * @if(theme_widget_area_has_content('sidebar_blog'))
 *   {!! app('widget')->renderArea('sidebar_blog') !!}
 * @endif
 */
if (!function_exists('theme_widget_area_has_content')) {
    function theme_widget_area_has_content(string $areaKey): bool
    {
        $area = WidgetArea::where('key', $areaKey)->first();
        if (!$area) {
            return false;
        }
        return $area->widgets()->exists();
    }
}

/**
 * Get the display label for a widget area.
 *
 * Usage in Blade: {{ theme_widget_area_label('footer_col_1') }}
 * Returns the registered name, or a humanized version of the key.
 */
function theme_widget_area_label(string $areaKey): string
{
    $definition = app('widget')->getArea($areaKey);
    return $definition['name'] ?? Str::headline(str_replace(['-', '_'], ' ', $areaKey));
}

/**
 * Get all resolved theme options.
 *
 * Returns an array of all theme options from the `theme_options` settings group.
 * Results are cached per-request (static variable).
 *
 * Usage: $options = theme_get_options();
 *        $color = $options['primary_color'] ?? '#4f46e5';
 *
 * Or for specific keys: $opts = theme_get_options(['primary_color', 'font_family']);
 */
function theme_get_options(?array $onlyKeys = null): array
{
    static $resolved = null;

    if ($resolved === null) {
        /** @var SettingsService $settings */
        $settings = app(SettingsService::class);
        $groupSettings = $settings->getGroupSettings('theme_options');

        $resolved = [];
        foreach ($groupSettings as $key => $definition) {
            $current = $definition['value'] ?? $definition['default'] ?? null;
            $resolved[$key] = $current;
        }

        // Apply theme.options.resolved filter (see Section 4)
        $resolved = Hook::applyFilters('theme.options.resolved', $resolved, $groupSettings);
    }

    if ($onlyKeys === null) {
        return $resolved;
    }

    return array_intersect_key($resolved, array_flip($onlyKeys));
}

/**
 * Get a single theme option by key.
 *
 * Usage in Blade: {{ theme_get_option('primary_color', '#4f46e5') }}
 */
function theme_get_option(string $key, $default = null)
{
    $options = theme_get_options();
    return $options[$key] ?? $default;
}

/**
 * Get the full permalink structure configuration.
 *
 * Returns an array like:
 * [
 *   'posts'      => ['single' => 'blog', 'archive' => 'blog'],
 *   'products'   => ['single' => 'product', 'archive' => 'shop'],
 *   'categories' => ['single' => 'category'],
 *   ...
 * ]
 *
 * Used by URL helper functions below.
 */
if (!function_exists('theme_permalink_structure')) {
    function theme_permalink_structure(): array
    {
        static $structure = null;

        if ($structure === null) {
            /** @var SettingsService $settings */
            $settings = app(SettingsService::class);
            $structure = $settings->getPermalinkStructure();
        }

        return $structure;
    }
}

/**
 * Get a single permalink segment.
 *
 * Usage: theme_permalink_segment('posts', 'single') → 'blog'
 *        theme_permalink_segment('products', 'archive') → 'shop'
 */
if (!function_exists('theme_permalink_segment')) {
    function theme_permalink_segment(string $group, string $context = 'single'): string
    {
        return data_get(theme_permalink_structure(), "{$group}.{$context}", '');
    }
}

/**
 * Generate a full permalink URL.
 *
 * Usage: theme_permalink_url('posts', 'my-post-slug') → 'http://site.com/blog/my-post-slug'
 *        theme_permalink_url('products', 'widget', 'archive') → 'http://site.com/shop/widget'
 */
if (!function_exists('theme_permalink_url')) {
    function theme_permalink_url(string $group, string $slug = '', string $context = 'single'): string
    {
        $segment = trim(theme_permalink_segment($group, $context), '/');
        $slug = trim($slug, '/');

        $parts = array_filter([$segment, $slug], fn($part) => $part !== '');
        $path = implode('/', $parts);

        return url($path);
    }
}

/**
 * Get an excerpt from a post.
 *
 * If the post has a manual excerpt, use it.
 * Otherwise, auto-generate from content_html by stripping tags.
 *
 * Usage in Blade: {{ the_excerpt($post, 120) }}
 */
if (!function_exists('the_excerpt')) {
    function the_excerpt($post, $length = 55)
    {
        if (!empty($post->excerpt)) {
            return $post->excerpt;
        }

        $content = strip_tags($post->content_html ?? '');

        if (strlen($content) <= $length) {
            return $content;
        }

        return substr($content, 0, $length) . '...';
    }
}

/**
 * Format a date using the site's configured date/time format.
 *
 * Usage in Blade: {{ format_post_date($post->created_at) }}
 *
 * Reads date_format and time_format from Settings.
 */
if (!function_exists('format_post_date')) {
    function format_post_date($date, $format = null)
    {
        if (!$date) {
            return '';
        }

        $settingsService = app(SettingsService::class);
        $dateFormat = $format ?? $settingsService->get('date_format', 'Y-m-d');
        $timeFormat = $settingsService->get('time_format', 'H:i');

        if (is_string($date)) {
            $date = \Carbon\Carbon::parse($date);
        }

        return $date->format($dateFormat . ' ' . $timeFormat);
    }
}

// ╔══════════════════════════════════════════════════════════════════╗
// ║ SECTION 12: LANDING BLOCK RENDERERS                             ║
// ╚══════════════════════════════════════════════════════════════════╝

/**
 * Register FlexiWhite-specific Landing Block renderers.
 * Each block type maps to a Blade view in themes/flexiwhite/resources/views/blocks/.
 *
 * Pattern: content.render.landing_block.{block_type}
 * Signature: function($html, $attrs) → rendered HTML string
 */
Hook::addFilter('content.render.landing_block.fw_hero_section', function ($html, $attrs) {
    return view('theme::blocks.hero', ['attrs' => $attrs])->render();
}, 10, 2);

Hook::addFilter('content.render.landing_block.fw_features_grid', function ($html, $attrs) {
    return view('theme::blocks.features_grid', ['attrs' => $attrs])->render();
}, 10, 2);

Hook::addFilter('content.render.landing_block.fw_stats_bar', function ($html, $attrs) {
    return view('theme::blocks.stats_bar', ['attrs' => $attrs])->render();
}, 10, 2);

Hook::addFilter('content.render.landing_block.fw_cta_section', function ($html, $attrs) {
    return view('theme::blocks.cta_section', ['attrs' => $attrs])->render();
}, 10, 2);

Hook::addFilter('content.render.landing_block.fw_latest_posts', function ($html, $attrs) {
    return view('theme::blocks.latest_posts', ['attrs' => $attrs])->render();
}, 10, 2);
