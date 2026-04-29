<?php

/**
 * FlexiDocs Functions
 *
 * Register hooks, filters, and helpers that extend the theme.
 */

use App\Facades\Hook;
use App\Services\WidgetManager;
use App\Services\SettingsService;

/**
 * Check if a template_theme value belongs to FlexiDocs (exact or namespaced).
 */
function isFlexidocsTemplate(?string $templateTheme): bool
{
    if (empty($templateTheme)) return false;
    return $templateTheme === 'flexidocs' || str_starts_with($templateTheme, 'flexidocs::');
}

// Theme activation hook
Hook::addAction('theme.activated', function ($theme) {
    if ($theme->slug === 'flexidocs') {
        // Theme activation tasks (e.g., set up options, custom metadata schema if system supports schema registration)
    }
});

/**
 * Register FlexiDocs custom frontend routes.
 *
 * This hook is the ONLY correct place for a theme to register its own routes.
 * Core PolyCMS fires 'routes.frontend.register' in routes/web.php,
 * allowing any active theme to inject routes without polluting the core router.
 */
Hook::addAction('routes.frontend.register', function () {
    try {
        $docsPrefix = get_option('flexidocs_doc_prefix', 'docs', 'theme_options');

        if (!empty(trim($docsPrefix, '/'))) {
            $prefix = '/' . trim($docsPrefix, '/');

            // Post route MUST come before category route (Laravel Router specificity)
            \Illuminate\Support\Facades\Route::get(
                $prefix . '/{categorySlug}/{postSlug}',
                [\App\Http\Controllers\Frontend\CategoryController::class, 'showDoc']
            )
                ->where('categorySlug', '[A-Za-z0-9\-_]+')
                ->where('postSlug', '[A-Za-z0-9\-_]+')
                ->name('theme.flexidocs.show');

            // Category route — also handles standalone flexidocs posts as fallback
            \Illuminate\Support\Facades\Route::get(
                $prefix . '/{slug}',
                function (string $slug, \Illuminate\Http\Request $request) use ($prefix) {
                    // 301 redirect: ?article=post-slug → /prefix/slug/post-slug
                    if ($request->has('article') && $request->get('article')) {
                        $articleSlug = $request->get('article');
                        return redirect(url($prefix . '/' . $slug . '/' . $articleSlug), 301);
                    }

                    // Try category first
                    $category = \App\Models\Category::where('slug', $slug)->first();
                    if ($category) {
                        return app(\App\Http\Controllers\Frontend\CategoryController::class)
                            ->show($slug, $request);
                    }

                    // Fallback: find a post with this slug that has a flexidocs template
                    $post = \App\Models\Post::with(['user', 'categories', 'tags', 'meta'])
                        ->where('slug', $slug)
                        ->where('status', 'published')
                        ->first();

                    if ($post && isFlexidocsTemplate($post->template_theme)) {
                        return app(\App\Http\Controllers\Frontend\PostController::class)
                            ->show($slug, $request);
                    }

                    abort(404);
                }
            )
                ->where('slug', '[A-Za-z0-9\-_]+')
                ->name('theme.flexidocs.category');
        }
    } catch (\Exception $e) {
        // Fail safely during install or CLI
    }
});

/**
 * Filter frontend URLs for Wiki categories and posts
 * We check if the item is explicitly assigned to 'flexidocs' layout theme
 */
Hook::addFilter('category.frontend_url', function ($url, $category) {
    $tt = $category->template_theme ?? '';
    if ($tt === 'flexidocs' || str_starts_with($tt, 'flexidocs::') || $category->layout_template_id === 'flexidocs') {
        $docsPrefix = get_option('flexidocs_doc_prefix', 'docs', 'theme_options');
        return '/' . $docsPrefix . '/' . $category->slug;
    }
    return $url;
}, 10, 2);

Hook::addFilter('post.frontend_url', function ($url, $post) {
    if ($post->type === 'post') {
        $docsPrefix = get_option('flexidocs_doc_prefix', 'docs', 'theme_options');

        // Check if the post's own template uses flexidocs (e.g. flexidocs::posts.iframe)
        if (isFlexidocsTemplate($post->template_theme ?? '')) {
            $category = $post->categories()->first();
            if ($category && (isFlexidocsTemplate($category->template_theme) || $category->layout_template_id === 'flexidocs')) {
                // Post inside a flexidocs category → /docs/cat-slug/post-slug
                return '/' . $docsPrefix . '/' . $category->slug . '/' . $post->slug;
            }
            // Standalone flexidocs post → /docs/post-slug
            return '/' . $docsPrefix . '/' . $post->slug;
        }

        // Check if the post's category uses flexidocs
        $category = $post->categories()->first();
        if ($category && (isFlexidocsTemplate($category->template_theme) || $category->layout_template_id === 'flexidocs')) {
            return '/' . $docsPrefix . '/' . $category->slug . '/' . $post->slug;
        }
    }
    return $url;
}, 10, 2);



/**
 * Canonical URL Override for FlexiDocs
 *
 * Priority chain: Core(default) → FlexiDocs(10) → Future MTOptimize(20+)
 *
 * Rules:
 * 1. Wiki page (/docs/cat/post): canonical = current URL (correct by default)
 *    UNLESS post has custom canonical_url meta → use that instead
 * 2. Blog page (/blog/post) where post belongs to flexidocs category:
 *    canonical = wiki URL (prevents duplicate content)
 */
Hook::addFilter('seo.canonical_url', function ($url) {
    $route = request()->route();
    if (!$route) {
        return $url;
    }

    $routeName = $route->getName();

    // Case 1: Wiki post page — check for per-post canonical override
    if ($routeName === 'theme.flexidocs.show') {
        $postSlug = $route->parameter('postSlug');
        if ($postSlug) {
            $post = \App\Models\Post::where('slug', $postSlug)
                ->where('status', 'published')
                ->first();
            if ($post) {
                $customCanonical = $post->getMeta('canonical_url');
                if (!empty($customCanonical)) {
                    return $customCanonical;
                }
            }
        }
        // Default: current wiki URL is already correct canonical
        return $url;
    }

    // Case 2: Blog post page — if post belongs to flexidocs category,
    // canonical should point to wiki URL to avoid duplicate content
    if ($routeName === 'posts.show') {
        $postSlug = $route->parameter('slug');
        if ($postSlug) {
            $post = \App\Models\Post::where('slug', $postSlug)
                ->where('status', 'published')
                ->with('categories')
                ->first();
            if ($post) {
                // Check per-post canonical override first
                $customCanonical = $post->getMeta('canonical_url');
                if (!empty($customCanonical)) {
                    return $customCanonical;
                }

                // Auto-canonical: if post category is flexidocs, point to wiki URL
                $category = $post->categories->first();
                if ($category && (isFlexidocsTemplate($category->template_theme) || $category->layout_template_id === 'flexidocs')) {
                    return url($post->frontend_url); // Uses the hook-overridden wiki URL
                }
            }
        }
    }

    return $url;
});


// Register widget areas
Hook::addAction('widgets.register_areas', function (WidgetManager $widgets): void {
    // Top Bar
    $widgets->registerArea('top_bar', [
        'name' => 'Top Bar',
        'description' => 'Top bar for navigation or announcements.',
        'order' => 110,
    ]);

    // Sidebar
    $widgets->registerArea('wiki_sidebar', [
        'name' => 'Wiki Sidebar Bottom',
        'description' => 'Appears at the bottom of the Wiki navigation tree.',
        'order' => 120,
    ]);
});

// Filter theme view data
Hook::addFilter('theme.view.data', function ($data, $viewName) {
    /** @var SettingsService $settingsService */
    $settingsService = app(SettingsService::class);

    $data['theme_name'] = 'FlexiDocs';
    $data['theme_version'] = '1.0.0';
    $data['site_title'] = $settingsService->get('site_title', 'PolyCMS Documentation');
    $data['tagline'] = $settingsService->get('tagline', 'Learn how to use our platform.');

    return $data;
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

// Register dynamic documentation route prefix setting
Hook::addFilter('settings.defaults', function ($defaults) {
    // FlexiDocs uniquely manages its own documentation routing namespace
    // This allows it to decouple from the main theme (e.g., FlexiMyta).
    $defaults['theme_options']['flexidocs_doc_prefix'] = [
        'key' => 'flexidocs_doc_prefix',
        'label' => 'Documentation Route Prefix',
        'description' => 'The customizable URL base for the wiki (e.g., "docs" for /docs/category/post)',
        'type' => 'string',
        'default' => 'docs',
        'group' => 'theme_options',
        'section' => 'flexidocs_settings',
        'section_label' => 'FlexiDocs Settings',
        'section_order' => 90,
    ];
    
    return $defaults;
});

/**
 * Register Article Feedback report
 *
 * This injects the report into the Core "Reports" menu group.
 */
Hook::addAction('admin.menu.build', function () {
    $menuRegistry = app(\App\Services\MenuRegistry::class);
    $menuRegistry->addChild('reports', [
        'key' => 'reports-article-feedback',
        'label' => 'Article Feedback',
        'route' => 'admin.reports.article-feedback',
        'icon' => null,
        'order' => 10,
    ]);
});
