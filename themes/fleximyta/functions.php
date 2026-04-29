<?php

/**
 * FlexiMyTa Theme Functions
 *
 * Register hooks, filters, and helpers that extend the FlexiMyTa theme.
 */

use App\Facades\Hook;
use App\Services\WidgetManager;
use App\Services\SettingsService;

// Theme activation hook
Hook::addAction('theme.activated', function ($theme) {
    if ($theme->slug === 'fleximyta') {
        // Theme activation tasks
    }
});

// Register widget areas
Hook::addAction('widgets.register_areas', function (WidgetManager $widgets): void {
    // Sidebar widget area
    $widgets->registerArea('sidebar', [
        'name' => 'Sidebar',
        'description' => 'Main sidebar widget area for blog posts and pages.',
        'order' => 110,
    ]);

    // Footer widget area
    $widgets->registerArea('footer', [
        'name' => 'Footer',
        'description' => 'Footer widget area.',
        'order' => 210,
    ]);
});

// Register theme settings
Hook::addFilter('settings.defaults', function ($defaults) {
    if (!isset($defaults['theme_options'])) {
        $defaults['theme_options'] = [];
    }
    
    $defaults['theme_options']['fleximyta_dark_mode_toggle'] = [
        'key'           => 'fleximyta_dark_mode_toggle',
        'label'         => _l('Show Dark/Light Mode Toggle'),
        'description'   => _l('Enable or disable the theme toggle button on the menu next to the cart.'),
        'type'          => 'toggle',
        'default'       => true,
        'group'         => 'theme_options',
        'section'       => 'fleximyta_options',
        'section_label' => _l('FlexiMyTa Settings'),
        'order'         => 10,
    ];
    
    return $defaults;
});

// Filter theme view data
Hook::addFilter('theme.view.data', function ($data, $viewName) {
    /** @var SettingsService $settingsService */
    $settingsService = app(SettingsService::class);

    $data['theme_name'] = 'FlexiMyTa';
    $data['theme_version'] = '1.0.0';
    $data['site_title'] = $settingsService->get('site_title', 'PolyCMS');
    $data['tagline'] = $settingsService->get('tagline', 'Just another PolyCMS site');

    // Home page detection
    $showOnFront = $settingsService->get('reading_show_on_front', 'posts');
    $homePageId = (int) $settingsService->get('reading_page_on_front');
    $data['is_front_page'] = false;
    
    if (isset($data['page']) && $showOnFront === 'page' && $data['page']->id === $homePageId) {
        $data['is_front_page'] = true;
    }

    return $data;
}, 10, 2);

// Filter to show/hide page header
Hook::addFilter('theme.show_page_header', function ($show, $page) {
    if (!$page) return $show;

    /** @var SettingsService $settingsService */
    $settingsService = app(SettingsService::class);
    $showOnFront = $settingsService->get('reading_show_on_front', 'posts');
    $homePageId = (int) $settingsService->get('reading_page_on_front');

    // Hide header if this page is set as the static front page
    if ($showOnFront === 'page' && (int)$page->id === $homePageId) {
        return false;
    }

    return $show;
}, 10, 2);

// Helper function: Get theme modification
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

// Helper function: Check if widget area has content
if (!function_exists('theme_widget_area_has_content')) {
    function theme_widget_area_has_content(string $areaKey): bool
    {
        $area = \App\Models\WidgetArea::where('key', $areaKey)->first();
        return $area && $area->widgets()->exists();
    }
}

// Helper function: Get excerpt
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

// Helper function: Format post date
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

if (!function_exists('theme_is_menu_active')) {
    /**
     * Determine if a menu item should be marked as active.
     * Supports intelligent matching (e.g., active "Blog" on single post pages).
     */
    function theme_is_menu_active($item): bool
    {
        $url = is_array($item) ? ($item['url'] ?? '#') : ($item->effective_url ?? '#');
        $currentUrl = request()->url();
        $fullUrl = request()->fullUrl();

        // 1. Exact match (normalized)
        if (rtrim($url, '/') === rtrim($currentUrl, '/') || rtrim($url, '/') === rtrim($fullUrl, '/')) {
            return true;
        }

        // 2. Intelligent Breadcrumb Match
        if (request()->routeIs('posts.show')) {
            try {
                $blogUrl = route('posts.index');
                if (rtrim($url, '/') === rtrim($blogUrl, '/')) return true;
            } catch (\Exception $e) {}
        }

        if (request()->routeIs('products.show')) {
            try {
                $shopUrl = route('products.index');
                if (rtrim($url, '/') === rtrim($shopUrl, '/')) return true;
            } catch (\Exception $e) {}
        }

        return false;
    }
}

// Register Landing Block Renderers
Hook::addFilter('content.render.landing_block.hero_section', function($html, $attrs) {
    return view('theme::blocks.hero', ['attrs' => $attrs])->render();
}, 10, 2);

Hook::addFilter('content.render.landing_block.pricing_matrix', function($html, $attrs, $context = []) {
    // Pricing matrix needs product context if available
    $product = $context['product'] ?? request()->route('product'); 
    return view('theme::blocks.pricing', [
        'attrs' => $attrs,
        'product' => $product
    ])->render();
}, 10, 3);

Hook::addFilter('content.render.landing_block.text_image', function($html, $attrs) {
    return view('theme::blocks.text_image', ['attrs' => $attrs])->render();
}, 10, 2);

Hook::addFilter('content.render.landing_block.divider', function($html, $attrs) {
    return view('theme::blocks.divider', ['attrs' => $attrs])->render();
}, 10, 2);

Hook::addFilter('content.render.landing_block.video', function($html, $attrs) {
    return view('theme::blocks.video', ['attrs' => $attrs])->render();
}, 10, 2);

Hook::addFilter('content.render.landing_block.html_block', function($html, $attrs) {
    return view('theme::blocks.html', ['attrs' => $attrs])->render();
}, 10, 2);

Hook::addFilter('content.render.landing_block.gallery', function($html, $attrs, $context = []) {
    return view('theme::blocks.gallery', [
        'attrs' => $attrs,
        'product' => $context['product'] ?? null,
        'post' => $context['post'] ?? null,
    ])->render();
}, 10, 3);

Hook::addFilter('content.render.landing_block.accordion', function($html, $attrs) {
    return view('theme::blocks.accordion', ['attrs' => $attrs])->render();
}, 10, 2);

Hook::addFilter('content.render.landing_block.tabs', function($html, $attrs) {
    return view('theme::blocks.tabs', ['attrs' => $attrs])->render();
}, 10, 2);

Hook::addFilter('content.render.landing_block.section', function($html, $attrs, $context, $renderer) {
    $childrenHtml = '';
    if (isset($attrs['blocks']) && is_array($attrs['blocks'])) {
        $childrenHtml = $renderer->render($attrs['blocks']);
    }
    return view('theme::blocks.section', [
        'attrs' => $attrs,
        'children' => $childrenHtml
    ])->render();
}, 10, 4);

Hook::addFilter('content.render.landing_block.row', function($html, $attrs, $context, $renderer) {
    return view('theme::blocks.row', [
        'attrs' => $attrs,
        'renderer' => $renderer
    ])->render();
}, 10, 4);

Hook::addFilter('content.render.landing_block.what_you_get', function($html, $attrs) {
    return view('theme::blocks.what_you_get', ['attrs' => $attrs])->render();
}, 10, 2);

Hook::addFilter('content.render.landing_block.features_grid', function($html, $attrs) {
    return view('theme::blocks.features_grid', ['attrs' => $attrs])->render();
}, 10, 2);

Hook::addFilter('content.render.landing_block.showcase', function($html, $attrs) {
    return view('theme::blocks.showcase', ['attrs' => $attrs])->render();
}, 10, 2);

Hook::addFilter('content.render.landing_block.cta_section', function($html, $attrs) {
    return view('theme::blocks.cta_section', ['attrs' => $attrs])->render();
}, 10, 2);

// Atomic Blocks
Hook::addFilter('content.render.landing_block.heading', function($html, $attrs) {
    return view('theme::blocks.heading', ['attrs' => $attrs])->render();
}, 10, 2);

Hook::addFilter('content.render.landing_block.text', function($html, $attrs) {
    return view('theme::blocks.text', ['attrs' => $attrs])->render();
}, 10, 2);

Hook::addFilter('content.render.landing_block.button', function($html, $attrs) {
    return view('theme::blocks.button', ['attrs' => $attrs])->render();
}, 10, 2);
/**
 * Global Landing Block Post-Renderer
 * Handles universal attributes like Viewport Full Width (Breakout)
 */
Hook::addFilter('content.render.landing_block.post', function($html, $type, $attrs) {
    $classes = [];
    $innerStyles = [];
    $isViewportFullWidth = !empty($attrs['viewport_full_width']);

    if ($isViewportFullWidth) {
        $classes[] = 'section-full-viewport';
    }

    if (!empty($attrs['margin'])) {
        $innerStyles[] = "margin: {$attrs['margin']}";
    }
    if (!empty($attrs['padding'])) {
        $innerStyles[] = "padding: {$attrs['padding']}";
    }

    if (empty($classes) && empty($innerStyles)) {
        return $html;
    }

    $classAttr = !empty($classes) ? ' class="' . implode(' ', $classes) . '"' : '';
    $innerStyleAttr = !empty($innerStyles) ? ' style="' . implode('; ', $innerStyles) . '"' : '';

    if ($isViewportFullWidth) {
        return "<div{$classAttr}><div{$innerStyleAttr}>{$html}</div></div>";
    }

    return "<div{$classAttr}{$innerStyleAttr}>{$html}</div>";
}, 10, 3);

Hook::addFilter('content.render.landing_block.image', function($html, $attrs) {
    return view('theme::blocks.image', ['attrs' => $attrs])->render();
}, 10, 2);

Hook::addFilter('content.render.landing_block.spacer', function($html, $attrs) {
    return view('theme::blocks.spacer', ['attrs' => $attrs])->render();
}, 10, 2);
