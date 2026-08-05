<?php

/**
 * Helper functions for PolyCMS
 */

if (!function_exists('theme_asset')) {
    /**
     * Get the URL to a theme asset
     *
     * @param string $path
     * @param string $type
     * @return string
     */
    function theme_asset(string $path, string $type = 'frontend'): string
    {
        $themeManager = app(\App\Services\ThemeManager::class);
        $activeTheme = $themeManager->getActiveTheme($type);

        if (!$activeTheme) {
            // Fallback to default assets
            return asset($path);
        }

        // Remove leading slash if present
        $path = ltrim($path, '/');

        // Return theme asset URL using route
        return route('theme.asset', ['themeSlug' => $activeTheme->slug, 'path' => $path]);
    }
}

if (!function_exists('_l')) {
    /**
     * Global helper function _l() - Similar to WordPress __()
     *
     * @param string $text The text to translate
     * @param array|string|null $replace Optional replacement array or locale override
     * @param string|null $locale Optional locale override if $replace is an array
     * @return string Translated text
     */
    function _l(string $text, array|string|null $replace = null, ?string $locale = null): string
    {
        return \App\Helpers\LanguageHelper::translate($text, $replace, $locale);
    }
}

if (!function_exists('_e')) {
    /**
     * Global helper function _e() - Similar to WordPress _e()
     *
     * @param string $text The text to translate and echo
     * @param array|string|null $replace Optional replacement array or locale override
     * @param string|null $locale Optional locale override if $replace is an array
     */
    function _e(string $text, array|string|null $replace = null, ?string $locale = null): void
    {
        \App\Helpers\LanguageHelper::echo($text, $replace, $locale);
    }
}

if (!function_exists('media_url')) {
    /**
     * Get variant URL (thumb, featured, or full) for any image URL or path.
     *
     * @param string|null $rawUrl
     * @param string $variant 'thumb' | 'featured' | 'full'
     * @return string|null
     */
    function media_url(?string $rawUrl, string $variant = 'featured'): ?string
    {
        if (empty($rawUrl)) {
            return null;
        }
        return app(\App\Services\MediaService::class)->getVariantUrl($rawUrl, $variant);
    }
}

if (!function_exists('is_media_lazy_loading_enabled')) {
    /**
     * Check if media lazy loading is enabled in Media Settings
     */
    function is_media_lazy_loading_enabled(): bool
    {
        return (bool) get_option('media_enable_lazy_loading', true, 'media');
    }
}

if (!function_exists('media_lazy_attr')) {
    /**
     * Get HTML attributes string for image lazy loading (loading="lazy" decoding="async" class="polycms-lazy-img")
     */
    function media_lazy_attr(): string
    {
        if (is_media_lazy_loading_enabled()) {
            return 'loading="lazy" decoding="async" class="polycms-lazy-img" onload="this.classList.add(\'polycms-loaded\')"';
        }
        return '';
    }
}

if (!function_exists('filter_content_lazy_images')) {
    /**
     * Automatically add loading="lazy" decoding="async" to <img> tags in HTML content string
     */
    function filter_content_lazy_images(?string $content): string
    {
        if (empty($content) || !is_media_lazy_loading_enabled()) {
            return $content ?? '';
        }

        return preg_replace_callback('/<img\b(?![^>]*\bloading=)([^>]*)>/i', function ($matches) {
            return '<img ' . media_lazy_attr() . ' ' . $matches[1] . '>';
        }, $content);
    }
}

if (!function_exists('get_default_post_image')) {
    /**
     * Retrieve the default post image URL.
     * Fallback chain: category default_featured_image → global default → null
     *
     * @param mixed $context Optional context (e.g. post object) for the filter.
     * @param string $variant 'featured' | 'thumb' | 'full'
     * @return string|null
     */
    function get_default_post_image(mixed $context = null, string $variant = 'featured'): ?string
    {
        $rawUrl = null;

        // Check category default_featured_image if context is a Post
        if ($context instanceof \App\Models\Post) {
            $categories = $context->relationLoaded('categories')
                ? $context->categories
                : $context->categories()->get();

            foreach ($categories as $category) {
                $meta = $category->meta ?? [];
                if (!empty($meta['default_featured_image'])) {
                    $rawUrl = $meta['default_featured_image'];
                    break;
                }
            }
        }

        if (empty($rawUrl)) {
            $rawUrl = get_option('reading_default_post_image', null, 'reading');
        }

        $rawUrl = apply_filters('post.default_image', $rawUrl, $context);
        return media_url($rawUrl, $variant);
    }
}


if (!function_exists('get_option')) {
    /**
     * Retrieve an option value stored in the settings table.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @param  string  $group
     * @return mixed
     */
    function get_option(string $key, mixed $default = null, string $group = 'core'): mixed
    {
        return \App\Support\OptionRepository::get($key, $default, $group);
    }
}

if (!function_exists('set_option')) {
    /**
     * Persist an option value.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @param  string  $group
     * @return void
     */
    function set_option(string $key, mixed $value, string $group = 'core'): void
    {
        \App\Support\OptionRepository::set($key, $value, $group);
    }
}

if (!function_exists('delete_option')) {
    /**
     * Remove an option by key.
     *
     * @param  string  $key
     * @param  string  $group
     * @return void
     */
    function delete_option(string $key, string $group = 'core'): void
    {
        \App\Support\OptionRepository::delete($key, $group);
    }
}

if (!function_exists('hook_manager')) {
    function hook_manager(): \App\Services\HookManager
    {
        if (!app()->bound(\App\Services\HookManager::class)) {
            app()->singleton(\App\Services\HookManager::class, fn () => new \App\Services\HookManager());
        }

        return app(\App\Services\HookManager::class);
    }
}

if (!function_exists('add_action')) {
    function add_action(string $hook, callable|string $callback, int $priority = 10, int $acceptedArgs = 1): void
    {
        hook_manager()->addAction($hook, $callback, $priority, $acceptedArgs);
    }
}

if (!function_exists('do_action')) {
    function do_action(string $hook, mixed ...$args): void
    {
        hook_manager()->doAction($hook, ...$args);
    }
}

if (!function_exists('add_filter')) {
    function add_filter(string $hook, callable|string $callback, int $priority = 10, int $acceptedArgs = 1): void
    {
        hook_manager()->addFilter($hook, $callback, $priority, $acceptedArgs);
    }
}

if (!function_exists('apply_filters')) {
    function apply_filters(string $hook, mixed $value, mixed ...$args): mixed
    {
        return hook_manager()->applyFilters($hook, $value, ...$args);
    }
}

if (!function_exists('remove_action')) {
    function remove_action(string $hook, callable|string|null $callback = null, int $priority = 10): bool
    {
        return hook_manager()->removeAction($hook, $callback, $priority);
    }
}

if (!function_exists('remove_filter')) {
    function remove_filter(string $hook, callable|string|null $callback = null, int $priority = 10): bool
    {
        return hook_manager()->removeFilter($hook, $callback, $priority);
    }
}

if (!function_exists('add_shortcode')) {
    function add_shortcode(string $tag, callable $callback): void
    {
        shortcode_manager()->add($tag, $callback);
    }
}

if (!function_exists('do_shortcode')) {
    function do_shortcode(string $content, bool $ignore_html = false): string
    {
        return shortcode_manager()->do($content);
    }
}

if (!function_exists('remove_shortcode')) {
    function remove_shortcode(string $tag): void
    {
        shortcode_manager()->remove($tag);
    }
}

if (!function_exists('remove_all_shortcodes')) {
    function remove_all_shortcodes(): void
    {
        shortcode_manager()->clear();
    }
}

if (!function_exists('shortcode_exists')) {
    function shortcode_exists(string $tag): bool
    {
        return shortcode_manager()->exists($tag);
    }
}

if (!function_exists('shortcode_parse_atts')) {
    function shortcode_parse_atts(string $text): array
    {
        return shortcode_manager()->parseAtts($text);
    }
}

if (!function_exists('shortcode_atts')) {
    function shortcode_atts(array $pairs, array $atts, string $shortcode = ''): array
    {
        return shortcode_manager()->mergeAtts($pairs, $atts, $shortcode);
    }
}

if (!function_exists('get_query_var')) {
    function get_query_var(string $key, mixed $default = null): mixed
    {
        return request()->query($key, $default);
    }
}

if (!function_exists('add_rewrite_rule')) {
    function add_rewrite_rule(string $regex, string $query, string $position = 'top'): void
    {
        // Not applicable in PolyCMS routing; provided for compatibility.
    }
}

if (!function_exists('add_rewrite_tag')) {
    function add_rewrite_tag(string $tag, string $regex): void
    {
        // Not applicable in PolyCMS routing; provided for compatibility.
    }
}

if (!function_exists('strip_shortcodes')) {
    function strip_shortcodes(string $content): string
    {
        return shortcode_manager()->strip($content);
    }
}

if (!function_exists('shortcode_manager')) {
    function shortcode_manager(): \App\Services\ShortcodeManager
    {
        if (!app()->bound(\App\Services\ShortcodeManager::class)) {
            app()->singleton(\App\Services\ShortcodeManager::class, fn () => new \App\Services\ShortcodeManager());
        }

        return app(\App\Services\ShortcodeManager::class);
    }
}

if (!function_exists('wp_get_current_user')) {
    function wp_get_current_user(): object
    {
        $user = auth()->user();

        if ($user) {
            $roles = [];

            if (method_exists($user, 'getRoleNames')) {
                $roles = $user->getRoleNames()->toArray();
            } elseif (property_exists($user, 'roles')) {
                $roles = is_array($user->roles) ? $user->roles : [];
            }

            return (object) [
                'id' => $user->id,
                'roles' => $roles,
            ];
        }

        return (object) [
            'id' => null,
            'roles' => [],
        ];
    }
}

if (!function_exists('theme_get_options')) {
    /**
     * Get all resolved theme options.
     *
     * @param array|null $onlyKeys
     * @return array
     */
    function theme_get_options(?array $onlyKeys = null): array
    {
        static $resolved = null;

        if ($resolved === null) {
            /** @var \App\Services\SettingsService $settings */
            $settings = app(\App\Services\SettingsService::class);
            $groupSettings = $settings->getGroupSettings('theme_options');

            $resolved = [];
            foreach ($groupSettings as $key => $definition) {
                $current = $definition['value'] ?? $definition['default'] ?? null;
                $resolved[$key] = $current;
            }

            // Apply theme.options.resolved filter
            $resolved = apply_filters('theme.options.resolved', $resolved, $groupSettings);
        }

        if ($onlyKeys === null) {
            return $resolved;
        }

        return array_intersect_key($resolved, array_flip($onlyKeys));
    }
}

if (!function_exists('theme_get_option')) {
    /**
     * Get a single theme option by key.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function theme_get_option(string $key, $default = null)
    {
        $options = theme_get_options();
        return $options[$key] ?? $default;
    }
}

if (!function_exists('theme_menu')) {
    /**
     * Get menu by location with all nested children (recursive, up to 10 levels)
     *
     * @param string $location
     * @return \App\Models\Menu|null
     */
    function theme_menu(string $location): ?\App\Models\Menu
    {
        // Get nested children relation recursively
        $getNestedChildrenRelation = function ($menuId, $depth = 0) use (&$getNestedChildrenRelation) {
            if ($depth >= 10) {
                return []; // Prevent infinite recursion (max 10 levels)
            }

            return [
                'children' => function ($query) use ($menuId, &$getNestedChildrenRelation, $depth) {
                    $query->where('active', true)
                        ->orderBy('order')
                        ->with($getNestedChildrenRelation($menuId, $depth + 1));
                }
            ];
        };

        $menu = \App\Models\Menu::where('location', $location)
            ->whereNull('deleted_at')
            ->first();

        if (!$menu) {
            return null;
        }

        // Load items with all nested children recursively
        $menu->load(['items' => function ($query) use ($menu, &$getNestedChildrenRelation) {
            $query->whereNull('parent_id')
                ->where('active', true)
                ->orderBy('order')
                ->with($getNestedChildrenRelation($menu->id, 0));
        }]);

        return $menu;
    }
}

if (!function_exists('theme_menu_by_slug')) {
    /**
     * Get menu by slug with all nested children (recursive, up to 10 levels)
     *
     * @param string $slug
     * @return \App\Models\Menu|null
     */
    function theme_menu_by_slug(string $slug): ?\App\Models\Menu
    {
        // Get nested children relation recursively
        $getNestedChildrenRelation = function ($menuId, $depth = 0) use (&$getNestedChildrenRelation) {
            if ($depth >= 10) {
                return []; // Prevent infinite recursion (max 10 levels)
            }

            return [
                'children' => function ($query) use ($menuId, &$getNestedChildrenRelation, $depth) {
                    $query->where('active', true)
                        ->orderBy('order')
                        ->with($getNestedChildrenRelation($menuId, $depth + 1));
                }
            ];
        };

        $menu = \App\Models\Menu::where('slug', $slug)
            ->whereNull('deleted_at')
            ->first();

        if (!$menu) {
            return null;
        }

        // Load items with all nested children recursively
        $menu->load(['items' => function ($query) use ($menu, &$getNestedChildrenRelation) {
            $query->whereNull('parent_id')
                ->where('active', true)
                ->orderBy('order')
                ->with($getNestedChildrenRelation($menu->id, 0));
        }]);

        return $menu;
    }
}

if (!function_exists('theme_render_menu')) {
    /**
     * Render menu HTML
     *
     * @param string $location
     * @param array $options
     * @return string
     */
    function theme_render_menu(string $location, array $options = []): string
    {
        $menu = theme_menu($location);
        if (!$menu || $menu->items->isEmpty()) {
            return '';
        }

        $wrapperClass = $options['wrapper_class'] ?? 'menu';
        $itemClass = $options['item_class'] ?? 'menu-item';
        $subMenuClass = $options['sub_menu_class'] ?? 'sub-menu';
        $linkClass = $options['link_class'] ?? 'menu-link';

        return theme_render_menu_items($menu->items, $wrapperClass, $itemClass, $subMenuClass, $linkClass);
    }
}

if (!function_exists('theme_render_menu_items')) {
    /**
     * Recursive helper to render menu items (supports unlimited nested levels)
     *
     * @param \Illuminate\Database\Eloquent\Collection $items
     * @param string $wrapperClass
     * @param string $itemClass
     * @param string $subMenuClass
     * @param string $linkClass
     * @param int $depth Current depth level (for preventing infinite recursion)
     * @return string
     */
    function theme_render_menu_items($items, string $wrapperClass, string $itemClass, string $subMenuClass, string $linkClass, int $depth = 0): string
    {
        if ($depth >= 10) {
            return ''; // Prevent infinite recursion (max 10 levels)
        }

        $html = "<ul class=\"{$wrapperClass}\">";

        foreach ($items as $item) {
            $url = $item->effective_url ?? '#';
            $target = $item->target === '_blank' ? ' target="_blank" rel="noopener"' : '';
            $cssClass = $item->css_class ? " {$item->css_class}" : '';
            $hasChildren = $item->children && $item->children->isNotEmpty();
            $isSearchItem = $item->type === 'search';
            $liClass = "{$itemClass}{$cssClass}" . ($hasChildren ? ' has-children' : '') . ($isSearchItem ? ' nav-item-search' : '');
            $liStyle = $isSearchItem ? ' style="display: flex; align-items: center;"' : '';

            $html .= "<li class=\"{$liClass}\"{$liStyle}>";

            if ($isSearchItem) {
                static $navSearchHelperCssOut = false;
                if (!$navSearchHelperCssOut) {
                    $navSearchHelperCssOut = true;
                    $html .= '<style>.nav-item-search{display:flex!important;align-items:center!important}.nav-item-search .widget{margin:0!important;padding:0!important;background:transparent!important;border:none!important;box-shadow:none!important}.nav-item-search .widget-blog-search{display:flex!important;align-items:center!important}.nav-item-search .widget-title{display:none!important}</style>';
                }

                $style = $item->search_style;
                $placeholder = $item->search_placeholder;

                $widgetInstance = new \App\Models\WidgetInstance();
                $widgetInstance->id = 'menu-item-' . $item->id;
                $widgetInstance->title = $item->title ?: _l('Search');
                $widgetInstance->config = [
                    'display_style' => $style,
                    'placeholder' => $placeholder,
                    'show_title' => false,
                    'suggestions_enabled' => true,
                    'suggestion_scope' => 'all',
                    'suggestion_limit' => 5,
                ];

                $widget = new \App\Widgets\BlogSearchWidget();
                $html .= $widget->render($widgetInstance);
            } elseif ($item->type === 'language') {
                $currentLangCode = \App\Helpers\LanguageHelper::getCurrentLanguage();
                $currentFlag = \App\Helpers\LanguageHelper::getFlagSvg($currentLangCode);
                $showLabel = $item->show_label;
                $activeLangs = \App\Helpers\LanguageHelper::getActiveLanguages();
                $currentLangModel = $activeLangs->firstWhere('code', $currentLangCode);
                $activeName = $currentLangModel ? ($currentLangModel->native_name ?: $currentLangModel->name) : $item->title;

                $html .= "<a href=\"" . e($url) . "\" class=\"{$linkClass} language-parent\"{$target} style=\"display: inline-flex; align-items: center; gap: 4px; vertical-align: middle;\">" . $currentFlag;
                if ($showLabel) {
                    $html .= " <span class=\"nav-link-label\">" . e($activeName) . "</span>";
                }
                $html .= "</a>";
            } elseif ($item->getAttribute('lang_code')) {
                $langCode = $item->getAttribute('lang_code');
                $flag = \App\Helpers\LanguageHelper::getFlagSvg($langCode);
                $html .= "<a href=\"" . e($url) . "\" class=\"{$linkClass}\"{$target}>" . $flag . " <span class=\"nav-link-label\">" . e($item->title) . "</span></a>";
            } else {
                $html .= "<a href=\"" . e($url) . "\" class=\"{$linkClass}\"{$target}>" . e($item->title) . "</a>";
            }

            if ($hasChildren) {
                // Recursively render nested children
                $html .= theme_render_menu_items($item->children, $subMenuClass, $itemClass, $subMenuClass, $linkClass, $depth + 1);
            }

            $html .= "</li>";
        }

        $html .= "</ul>";

        return $html;
    }
}

if (!function_exists('theme_render_menu_item')) {
    /**
     * Render a single menu item with its nested children (recursive)
     * Useful for custom menu rendering in themes
     *
     * @param \App\Models\MenuItem $item
     * @param array $options Rendering options
     * @param int $depth Current depth level
     * @return string
     */
    function theme_render_menu_item($item, array $options = [], int $depth = 0): string
    {
        if ($depth >= 10) {
            return ''; // Prevent infinite recursion
        }

        $url = $item->effective_url ?? '#';
        $target = $item->target === '_blank' ? ' target="_blank" rel="noopener"' : '';
        $cssClass = $item->css_class ? " {$item->css_class}" : '';
        $itemClass = $options['item_class'] ?? 'menu-item';
        $linkClass = $options['link_class'] ?? 'menu-link';
        $subMenuClass = $options['sub_menu_class'] ?? 'sub-menu';
        $wrapperTag = $options['wrapper_tag'] ?? 'div'; // 'div' or 'li'
        $hasChildren = $item->children && $item->children->isNotEmpty();
        $hasChildrenClass = $hasChildren ? ($options['has_children_class'] ?? 'has-children') : '';
        $isSearchItem = $item->type === 'search';

        // Check active state
        $isActive = false;
        if (isset($options['check_active']) && $options['check_active']) {
            $isActive = theme_is_menu_active($item);
        }
        $activeClass = $isActive ? ($options['active_class'] ?? 'active') : '';

        $html = "<{$wrapperTag} class=\"{$itemClass}{$cssClass} {$hasChildrenClass} {$activeClass}" . ($isSearchItem ? ' nav-item-search' : '') . "\"";
        if ($isSearchItem) {
            $html .= ' style="display: flex; align-items: center;"';
        }
        $html .= '>';

        if ($isSearchItem) {
            static $navSearchHelperSingleCssOut = false;
            if (!$navSearchHelperSingleCssOut) {
                $navSearchHelperSingleCssOut = true;
                $html .= '<style>.nav-item-search{display:flex!important;align-items:center!important}.nav-item-search .widget{margin:0!important;padding:0!important;background:transparent!important;border:none!important;box-shadow:none!important}.nav-item-search .widget-blog-search{display:flex!important;align-items:center!important}.nav-item-search .widget-title{display:none!important}</style>';
            }

            $style = $item->search_style;
            $placeholder = $item->search_placeholder;

            $widgetInstance = new \App\Models\WidgetInstance();
            $widgetInstance->id = 'menu-item-' . $item->id;
            $widgetInstance->title = $item->title ?: _l('Search');
            $widgetInstance->config = [
                'display_style' => $style,
                'placeholder' => $placeholder,
                'show_title' => false,
                'suggestions_enabled' => true,
                'suggestion_scope' => 'all',
                'suggestion_limit' => 5,
            ];

            $widget = new \App\Widgets\BlogSearchWidget();
            $html .= $widget->render($widgetInstance);
        } elseif ($item->type === 'language') {
            $currentLangCode = \App\Helpers\LanguageHelper::getCurrentLanguage();
            $currentFlag = \App\Helpers\LanguageHelper::getFlagSvg($currentLangCode);
            $showLabel = $item->show_label;
            $activeLangs = \App\Helpers\LanguageHelper::getActiveLanguages();
            $currentLangModel = $activeLangs->firstWhere('code', $currentLangCode);
            $activeName = $currentLangModel ? ($currentLangModel->native_name ?: $currentLangModel->name) : $item->title;
            
            $html .= "<a href=\"" . e($url) . "\" class=\"{$linkClass} language-parent\"{$target} style=\"display: inline-flex; align-items: center; gap: 4px; vertical-align: middle;\">" . $currentFlag;
            if ($showLabel) {
                $html .= " <span class=\"nav-link-label\">" . e($activeName) . "</span>";
            }
            $html .= "</a>";
        } elseif ($item->getAttribute('lang_code')) {
            $langCode = $item->getAttribute('lang_code');
            $flag = \App\Helpers\LanguageHelper::getFlagSvg($langCode);
            $html .= "<a href=\"" . e($url) . "\" class=\"{$linkClass}\"{$target}>" . $flag . " <span class=\"nav-link-label\">" . e($item->title) . "</span></a>";
        } else {
            $html .= "<a href=\"" . e($url) . "\" class=\"{$linkClass}\"{$target}>" . e($item->title) . "</a>";
        }

        if ($hasChildren) {
            $html .= "<ul class=\"{$subMenuClass}\">";
            foreach ($item->children as $child) {
                $html .= theme_render_menu_item($child, $options, $depth + 1);
            }
            $html .= "</ul>";
        }

        $html .= "</{$wrapperTag}>";

        return $html;
    }
}

if (!function_exists('theme_is_menu_active')) {
    /**
     * Determine if a menu item should be marked as active.
     * Supports intelligent matching (e.g., active "Blog" on single post pages).
     *
     * @param \App\Models\MenuItem|array $item
     * @return bool
     */
    function theme_is_menu_active($item): bool
    {
        $url = is_array($item) ? ($item['url'] ?? '#') : ($item->effective_url ?? '#');
        $currentUrl = request()->url();
        $fullUrl = request()->fullUrl();
        $normalizedMenuUrl = rtrim($url, '/');

        // 1. Exact match (normalized)
        if ($normalizedMenuUrl === rtrim($currentUrl, '/') || $normalizedMenuUrl === rtrim($fullUrl, '/')) {
            return true;
        }

        // 2. Intelligent Breadcrumb Match
        
        // Post Category Match
        if (request()->routeIs('categories.show')) {
            $category = request()->route('category');
            if ($category && isset($category->breadcrumb)) {
                foreach ($category->breadcrumb as $ancestor) {
                    if ($normalizedMenuUrl === rtrim($ancestor->frontend_url ?? '', '/')) {
                        return true;
                    }
                }
            }
        }
        
        // Product Category Match
        if (request()->routeIs('product_categories.show')) {
            $category = request()->route('category') ?? request()->route('product_category');
            if ($category && isset($category->breadcrumb)) {
                foreach ($category->breadcrumb as $ancestor) {
                    if ($normalizedMenuUrl === rtrim($ancestor->frontend_url ?? '', '/')) {
                        return true;
                    }
                }
            }
        }

        // If viewing a single post
        if (request()->routeIs('posts.show')) {
            $post = request()->route('post');
            if ($post && isset($post->categories)) {
                foreach ($post->categories as $cat) {
                    if (isset($cat->breadcrumb)) {
                        foreach ($cat->breadcrumb as $ancestor) {
                            if ($normalizedMenuUrl === rtrim($ancestor->frontend_url ?? '', '/')) {
                                return true;
                            }
                        }
                    }
                }
            }
            try {
                $blogUrl = route('posts.index');
                if ($normalizedMenuUrl === rtrim($blogUrl, '/')) {
                    return true;
                }
            } catch (\Exception $e) {}
        }

        // If viewing a single product
        if (request()->routeIs('products.show')) {
            $product = request()->route('product');
            if ($product && isset($product->categories)) {
                foreach ($product->categories as $cat) {
                    if (isset($cat->breadcrumb)) {
                        foreach ($cat->breadcrumb as $ancestor) {
                            if ($normalizedMenuUrl === rtrim($ancestor->frontend_url ?? '', '/')) {
                                return true;
                            }
                        }
                    }
                }
            }
            try {
                $shopUrl = route('products.index');
                if ($normalizedMenuUrl === rtrim($shopUrl, '/')) {
                    return true;
                }
            } catch (\Exception $e) {}
        }

        return false;
    }
}

if (!function_exists('theme_permalink_structure')) {
    /**
     * Get the permalink structure from settings.
     */
    function theme_permalink_structure(): array
    {
        static $structure = null;
        if ($structure === null) {
            /** @var \App\Services\SettingsService $settings */
            $settings = app(\App\Services\SettingsService::class);
            $structure = $settings->getPermalinkStructure();
        }
        return $structure;
    }
}

if (!function_exists('theme_permalink_url')) {
    /**
     * Generate a permalink URL based on the current structure.
     */
    function theme_permalink_url(string $group, string $slug = '', string $context = 'single'): string
    {
        $structure = theme_permalink_structure();
        $segment = trim(data_get($structure, "{$group}.{$context}", ''), '/');
        $slug = trim($slug, '/');

        $parts = array_filter([$segment, $slug], fn ($part) => $part !== '');
        $path = implode('/', $parts);

        // Prepend locale prefix if not default language
        $currentLocale = \App\Helpers\LanguageHelper::getCurrentLanguage();
        $isDefault = cache()->remember("is_default_locale_{$currentLocale}", 3600, function () use ($currentLocale) {
            try {
                if (!\Illuminate\Support\Facades\Schema::hasTable('languages')) {
                    return true;
                }
                return (bool) \App\Models\Language::where('code', $currentLocale)->where('is_default', true)->exists();
            } catch (\Exception $e) {
                return true;
            }
        });

        if (!$isDefault) {
            $path = $currentLocale . '/' . $path;
        }

        return url($path);
    }
}

if (!function_exists('get_category_url')) {
    /**
     * Get the URL for a category (post category)
     *
     * @param string|object $category Category slug or Category model
     * @return string
     */
    function get_category_url($category): string
    {
        $slug = is_string($category) ? $category : ($category->slug ?? '');
        return theme_permalink_url('categories', $slug, 'single');
    }
}

if (!function_exists('get_product_category_url')) {
    /**
     * Get the URL for a product category
     *
     * @param string|object $category Category slug or ProductCategory model
     * @return string
     */
    function get_product_category_url($category): string
    {
        $slug = is_string($category) ? $category : ($category->slug ?? '');
        return theme_permalink_url('product_categories', $slug, 'single');
    }
}

if (!function_exists('get_product_brand_url')) {
    /**
     * Get the URL for a product brand
     *
     * @param string|object $brand Brand slug or ProductBrand model
     * @return string
     */
    function get_product_brand_url($brand): string
    {
        $slug = is_string($brand) ? $brand : ($brand->slug ?? '');
        return theme_permalink_url('product_brands', $slug, 'single');
    }
}

if (!function_exists('get_post_tag_url')) {
    /**
     * Get the URL for a post tag
     *
     * @param string|object $tag Tag slug or PostTag model
     * @return string
     */
    function get_post_tag_url($tag): string
    {
        $slug = is_string($tag) ? $tag : ($tag->slug ?? '');
        return theme_permalink_url('tags', $slug, 'post');
    }
}

if (!function_exists('get_product_tag_url')) {
    /**
     * Get the URL for a product tag
     *
     * @param string|object $tag Tag slug or ProductTag model
     * @return string
     */
    function get_product_tag_url($tag): string
    {
        $slug = is_string($tag) ? $tag : ($tag->slug ?? '');
        return theme_permalink_url('tags', $slug, 'product');
    }
}

if (!function_exists('get_author_url')) {
    /**
     * Get the URL for an author archive
     *
     * @param string|object $user Username/slug or User model
     * @return string
     */
    function get_author_url($user): string
    {
        $slug = is_string($user) ? $user : ($user->username ?? $user->slug ?? '');
        return theme_permalink_url('users', $slug, 'single');
    }
}

if (!function_exists('format_currency')) {
    /**
     * Format a number as a currency string according to settings.
     *
     * @param float|int $amount
     * @param string|null $currency
     * @return string
     */
    function format_currency($amount, ?string $currency = null): string
    {
        $settings = app(\App\Services\SettingsService::class);
        $currencyCode = $currency ?? $settings->get('ecommerce_currency', 'USD');
        
        // Default values from legacy/global settings
        $currencySymbol = $settings->get('ecommerce_currency_symbol', '$');
        $decimals = (int) $settings->get('currency_decimals', 2);
        $thousandsSeparator = $settings->get('currency_thousands_separator', ',');
        $decimalSeparator = $settings->get('currency_decimal_separator', '.');
        $symbolPosition = $settings->get('currency_symbol_position', 'before');
        $addSpace = (bool) $settings->get('currency_space', false);

        // Try to find specific currency settings
        $currencies = $settings->get('currencies');
        if ($currencies) {
             $currenciesArray = is_string($currencies) ? json_decode($currencies, true) : $currencies;
             if (is_array($currenciesArray)) {
                 foreach ($currenciesArray as $c) {
                     if (strtoupper($c['code']) === strtoupper($currencyCode)) {
                         $currencySymbol = $c['symbol'] ?? $currencySymbol;
                         $decimals = isset($c['decimals']) ? (int)$c['decimals'] : $decimals;
                         $thousandsSeparator = $c['thousands_separator'] ?? $thousandsSeparator;
                         $decimalSeparator = $c['decimal_separator'] ?? $decimalSeparator;
                         $symbolPosition = $c['symbol_position'] ?? $symbolPosition;
                         $addSpace = isset($c['space_between']) ? (bool)$c['space_between'] : $addSpace;
                         
                         // Apply Exchange Rate
                         if (isset($c['rate']) && is_numeric($c['rate']) && $c['rate'] > 0) {
                             $amount = $amount * (float)$c['rate'];
                         }
                         
                         break;
                     }
                 }
             }
        }

        $formattedAmount = number_format((float) $amount, $decimals, $decimalSeparator, $thousandsSeparator);
        
        if (str_contains($formattedAmount, $decimalSeparator)) {
            $formattedAmount = rtrim($formattedAmount, '0');
            $formattedAmount = rtrim($formattedAmount, $decimalSeparator);
        }
        
        $space = $addSpace ? ' ' : '';

        if ($symbolPosition === 'after') {
            return $formattedAmount . $space . $currencySymbol;
        }

        return $currencySymbol . $space . $formattedAmount;
    }
}

if (!function_exists('render_dynamic_blocks')) {
    /**
     * Parse HTML content and replace static Pricing Matrix blocks with dynamic renderings.
     * This ensures currency formatting is always up-to-date even in stored HTML content.
     *
     * @param string $content
     * @return string
     */
    function render_dynamic_blocks(string $content): string
    {
        if (empty($content)) {
            return '';
        }

        if (str_contains($content, '[')) {
            $content = do_shortcode($content);
        }

        if (!str_contains($content, 'pricing-matrix-') && 
            !str_contains($content, 'data-type="landing-block"') && 
            !str_contains($content, 'data-block-type="') &&
            !str_contains($content, '<!-- landing_block:') &&
            !str_contains($content, 'data-youtube-gallery') &&
            !str_contains($content, 'language-mermaid') &&
            !str_contains($content, 'data-type="modal-link"') &&
            !str_contains($content, 'data-modal-link') &&
            !str_contains($content, 'data-type="direct-iframe"')) {
            return $content;
        }

        // 1. Handle legacy static HTML format: <section class="pricing" id="pricing-matrix-[ID]" ...>
        $content = preg_replace_callback('/<section[^>]*id="pricing-matrix-([0-9]+)"[^>]*>.*?<\/section>(\s*<p><\/p>)?/s', function ($matches) {
            $productId = (int) $matches[1];
            $fullHtml = $matches[0];
            
            // Try to detect style
            $style = 'cards';
            if (str_contains($fullHtml, 'pricing-table-container')) {
                $style = 'table';
            } elseif (str_contains($fullHtml, 'pricing-list')) {
                $style = 'list';
            }
            
            $product = \App\Models\Product::find($productId);
            if (!$product) {
                return $fullHtml;
            }
            
            try {
                return view('theme::blocks.pricing', [
                    'product' => $product,
                    'attrs' => ['style' => $style]
                ])->render();
            } catch (\Exception $e) {
                return $fullHtml;
            }
        }, $content);

        // 2. Handle modern placeholder format: <div data-block-type="[BLOCK_TYPE]" ...>
        $content = preg_replace_callback('/<div[^>]*data-block-type="([a-zA-Z0-9_\-]+)"[^>]*>.*?<\/div>/s', function ($matches) {
            $html = $matches[0];
            $blockType = $matches[1];
            
            // Extract block data JSON
            $data = [];
            if (preg_match('/data-block-data=\'([^\']+)\'/', $html, $dataMatch)) {
                $data = json_decode($dataMatch[1], true) ?? [];
            } elseif (preg_match('/data-block-data="([^"]+)"/', $html, $dataMatch)) {
                $data = json_decode(htmlspecialchars_decode($dataMatch[1]), true) ?? [];
            }
            
            $hookName = 'content.render.landing_block.' . $blockType;
            $context = [];
            $product = request()->route('product');
            if ($product) {
                $context['product'] = $product;
            }

            $rendered = \App\Facades\Hook::applyFilters($hookName, '', $data, $context);
            if (!empty($rendered)) {
                return $rendered;
            }

            if ($blockType === 'pricing_matrix') {
                if (!$product && isset($data['product_id'])) {
                    $product = \App\Models\Product::find($data['product_id']);
                }
                if ($product) {
                    try {
                        return view('theme::blocks.pricing', [
                            'product' => $product,
                            'attrs' => $data
                        ])->render();
                    } catch (\Exception $e) {}
                }
            }

            return $html;
        }, $content);

        // 3. Handle Xem Tuoi Xong Dat Block
        $content = preg_replace_callback('/<div[^>]*data-block-type="xem_tuoi_xong_dat"[^>]*>.*?<\/div>/s', function ($matches) {
             $html = $matches[0];

             // Extract block data JSON
             $data = [];
             if (preg_match('/data-block-data=\'([^\']+)\'/', $html, $dataMatch)) {
                 $data = json_decode($dataMatch[1], true) ?? [];
             } elseif (preg_match('/data-block-data="([^"]+)"/', $html, $dataMatch)) {
                 $data = json_decode(htmlspecialchars_decode($dataMatch[1]), true) ?? [];
             }

             // Render the lookup form view
             try {
                 if (view()->exists('xemtuoixongdat::shortcodes.lookup-form')) {
                     return view('xemtuoixongdat::shortcodes.lookup-form', $data)->render();
                 }
                 // specific fallback or error logging
                 return '<!-- View not found: xemtuoixongdat::shortcodes.lookup-form -->';
             } catch (\Exception $e) {
                 return '<!-- Error rendering block: ' . $e->getMessage() . ' -->';
             }
        }, $content);

        // 4. Handle Landing Block markers: <!-- landing_block:{"type":"...","attrs":{...}} -->
        $content = preg_replace_callback('/<!-- landing_block:(\{.*?\}) -->/s', function ($matches) {
            $blockData = json_decode($matches[1], true);
            if (!$blockData || empty($blockData['type'])) {
                return $matches[0]; // Return original if JSON invalid
            }

            $type = $blockData['type'];
            $attrs = $blockData['attrs'] ?? [];
            $hookName = 'content.render.landing_block.' . $type;

            // Dispatch to theme-registered renderer
            $rendered = \App\Facades\Hook::applyFilters($hookName, '', $attrs);

            // If no renderer handled it, return the raw comment
            return $rendered ?: $matches[0];
        }, $content);

        // 5. Handle YouTube Gallery placeholder format: <div[^>]*data-youtube-gallery[^>]*>.*?</div>
        $content = preg_replace_callback('/<div[^>]*data-youtube-gallery[^>]*>.*?<\/div>/s', function ($matches) {
            $divTag = $matches[0];
            
            preg_match_all('/([a-zA-Z0-9\-]+)\s*=\s*([\'"])(.*?)\2/is', $divTag, $attrMatches, PREG_SET_ORDER);
            
            $attrs = [];
            foreach ($attrMatches as $match) {
                $key = $match[1];
                $val = html_entity_decode($match[3], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $attrs[$key] = $val;
            }
            
            $urls = [];
            if (isset($attrs['data-urls'])) {
                $urlsDecoded = json_decode($attrs['data-urls'], true);
                if (is_array($urlsDecoded)) {
                    $urls = $urlsDecoded;
                }
            }
            
            $layout = $attrs['layout'] ?? 'grid';
            $visibleItems = isset($attrs['data-slider-visible-items']) ? (int)$attrs['data-slider-visible-items'] : 1;
            $autoPlay = isset($attrs['data-slider-autoplay']) ? filter_var($attrs['data-slider-autoplay'], FILTER_VALIDATE_BOOLEAN) : false;
            $continuous = isset($attrs['data-slider-continuous']) ? filter_var($attrs['data-slider-continuous'], FILTER_VALIDATE_BOOLEAN) : false;
            $direction = $attrs['data-slider-direction'] ?? 'left';
            
            $blocks = [
                [
                    'type' => 'youtubeGallery',
                    'attrs' => [
                        'urls' => $urls,
                        'layout' => $layout,
                        'sliderVisibleItems' => $visibleItems,
                        'sliderAutoPlay' => $autoPlay,
                        'sliderContinuous' => $continuous,
                        'sliderDirection' => $direction,
                    ]
                ]
            ];
            
            return app(\App\Services\ContentRenderer::class)->render($blocks);
        }, $content);

        // 6. Handle Mermaid diagrams: <pre><code class="language-mermaid">...</code></pre>
        if (str_contains($content, 'language-mermaid')) {
            $content = preg_replace_callback('/<pre><code class="language-mermaid">(.*?)<\/code><\/pre>/is', function ($matches) {
                $code = html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $code = trim($code);
                return '<div class="mermaid" style="display: flex; justify-content: center; margin: 2rem 0; width: 100%;">' . $code . '</div>';
            }, $content);

            if (!str_contains($content, 'mermaid.min.js"></script>')) {
                $mermaidUrl = asset('assets/vendor/mermaid-10.x/mermaid.min.js');
                $mermaidScript = '
<script src="' . htmlspecialchars($mermaidUrl, ENT_QUOTES, 'UTF-8') . '"></script>
<script>
    (function() {
        if (typeof mermaid !== "undefined") {
            var isDark = document.documentElement.classList.contains("dark");
            var themeVars = isDark ? {
                background: "#0a0a0a",
                primaryColor: "#0a0a0a",
                primaryTextColor: "#ededed",
                primaryBorderColor: "#333333",
                lineColor: "#444444",
                secondaryColor: "#1f1f1f",
                tertiaryColor: "#121212",
                noteBkgColor: "#1f1f1f",
                noteTextColor: "#ededed",
                actorBkg: "#0a0a0a",
                actorBorder: "#333333",
                actorTextColor: "#ededed",
                signalColor: "#ededed",
                signalTextColor: "#a0a0a0",
                labelBoxBkgColor: "#0a0a0a",
                labelBoxBorderColor: "#333333",
                labelTextColor: "#a0a0a0",
                loopBkgColor: "#121212",
                loopBorderColor: "#333333",
                fontSize: "12px",
                fontFamily: "Inter, system-ui, -apple-system, sans-serif"
            } : {
                background: "#ffffff",
                primaryColor: "#ffffff",
                primaryTextColor: "#111111",
                primaryBorderColor: "#e2e8f0",
                lineColor: "#cbd5e1",
                secondaryColor: "#f8fafc",
                tertiaryColor: "#f1f5f9",
                noteBkgColor: "#f8fafc",
                noteTextColor: "#111111",
                actorBkg: "#ffffff",
                actorBorder: "#e2e8f0",
                actorTextColor: "#111111",
                signalColor: "#111111",
                signalTextColor: "#475569",
                labelBoxBkgColor: "#ffffff",
                labelBoxBorderColor: "#e2e8f0",
                labelTextColor: "#475569",
                loopBkgColor: "#f8fafc",
                loopBorderColor: "#e2e8f0",
                fontSize: "12px",
                fontFamily: "Inter, system-ui, -apple-system, sans-serif"
            };
            mermaid.initialize({
                startOnLoad: true,
                theme: "base",
                themeVariables: themeVars,
                securityLevel: "loose",
                flowchart: { useMaxWidth: true, htmlLabels: true }
            });
        }
    })();
</script>';
                $content .= $mermaidScript;
            }
        }

        // 7. Handle Modal Link placeholder format: <div/a/span[^>]*(data-modal-link|data-type="modal-link")[^>]*>.*?</div>/</a>/</span>
        $content = preg_replace_callback('/<(div|a|span)[^>]*?(?:data-modal-link|data-type="modal-link")[^>]*?>(.*?)<\/\1>/is', function ($matches) {
            $tagOpen = $matches[0];
            // Extract attributes from the opening tag only to prevent conflicts with nested child attributes
            $openingTagEnd = strpos($tagOpen, '>');
            $openingTag = $openingTagEnd !== false ? substr($tagOpen, 0, $openingTagEnd + 1) : $tagOpen;
            
            preg_match_all('/([a-zA-Z0-9\-]+)\s*=\s*([\'"])(.*?)\2/is', $openingTag, $attrMatches, PREG_SET_ORDER);
            
            $attrs = [];
            foreach ($attrMatches as $match) {
                $key = $match[1];
                $val = html_entity_decode($match[3], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $attrs[$key] = $val;
            }
            
            $innerContent = isset($matches[3]) ? trim($matches[3]) : '';
            $labelText = $attrs['data-label-text'] ?? ($innerContent ?: 'Click here');
            $modalSize = $attrs['data-modal-size'] ?? 'lg';
            $contentType = $attrs['data-content-type'] ?? 'html';
            $contentHtml = $attrs['data-content-html'] ?? '';
            $iframeUrl = $attrs['data-iframe-url'] ?? '';
            $displayMode = $attrs['data-display-mode'] ?? 'button';
            
            $blocks = [
                [
                    'type' => 'modalLink',
                    'attrs' => [
                        'labelText' => $labelText,
                        'modalSize' => $modalSize,
                        'contentType' => $contentType,
                        'contentHtml' => $contentHtml,
                        'iframeUrl' => $iframeUrl,
                        'displayMode' => $displayMode,
                    ]
                ]
            ];
            
            return app(\App\Services\ContentRenderer::class)->render($blocks);
        }, $content);

        // 8. Handle Direct IFrame placeholder format: <div[^>]*data-type="direct-iframe"[^>]*>.*?</div>
        $content = preg_replace_callback('/<div[^>]*data-type="direct-iframe"[^>]*>.*?<\/div>/s', function ($matches) {
            $divTag = $matches[0];
            
            preg_match_all('/([a-zA-Z0-9\-]+)\s*=\s*([\'"])(.*?)\2/is', $divTag, $attrMatches, PREG_SET_ORDER);
            
            $attrs = [];
            foreach ($attrMatches as $match) {
                $key = $match[1];
                $val = html_entity_decode($match[3], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $attrs[$key] = $val;
            }
            
            $src = $attrs['data-src'] ?? '';
            $height = $attrs['data-height'] ?? '500';
            
            if (!$src) {
                return '';
            }
            
            $srcHtml = htmlspecialchars($src, ENT_QUOTES, 'UTF-8');
            $heightHtml = htmlspecialchars($height, ENT_QUOTES, 'UTF-8');
            
            return '
            <div class="my-8 rounded-xl overflow-hidden border border-gray-200 dark:border-slate-700 shadow-sm bg-white dark:bg-slate-800">
                <div class="bg-gray-50 dark:bg-slate-800/50 px-4 py-3 border-b border-gray-200 dark:border-slate-700 flex items-center justify-between" style="padding: 12px 16px; border-bottom: 1px solid rgba(226,232,240,0.8); display: flex; align-items: center; justify-content: space-between;">
                    <div class="flex items-center gap-2" style="display: flex; align-items: center; gap: 8px;">
                        <span style="display: inline-block; width: 12px; height: 12px; border-radius: 50%; background: #f87171;"></span>
                        <span style="display: inline-block; width: 12px; height: 12px; border-radius: 50%; background: #fbbf24;"></span>
                        <span style="display: inline-block; width: 12px; height: 12px; border-radius: 50%; background: #34d399;"></span>
                        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 font-mono" style="margin-left: 8px; font-size: 12px; font-family: monospace; font-weight: 600;">Monthly Expense vs Payment Report</span>
                    </div>
                    <span class="text-[11px] font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider" style="font-size: 11px; font-weight: 700; color: #2563eb; text-transform: uppercase; letter-spacing: 0.05em;">Live Embed</span>
                </div>
                <iframe src="' . $srcHtml . '" class="w-full border-0" style="height: ' . $heightHtml . 'px; display: block; width: 100%; border: none;" allowfullscreen></iframe>
            </div>';
        }, $content);

        // 9. Process shortcodes if ProjectHub is enabled, otherwise strip them
        if (app(\App\Services\ModuleManager::class)->isModuleEnabled('Polyx.ProjectHub')) {
            // Decode html-escaped quotes inside shortcodes (e.g. &quot; -> ") so shortcode parser can process attributes correctly
            $content = preg_replace_callback('/\[[^\]]+\]/', function($matches) {
                return str_replace('&quot;', '"', $matches[0]);
            }, $content);
            $content = do_shortcode($content);
        } else {
            $content = preg_replace('/\[project_hub_roadmap[^\]]*\]/i', '', $content);
            $content = preg_replace('/\[project_hub_release_banner[^\]]*\]/i', '', $content);
            $content = preg_replace('/\[project_hub_chart[^\]]*\]/i', '', $content);
        }

        return $content;
    }
}

if (!function_exists('get_link_target')) {
    /**
     * Determine the link target based on domain.
     * Returns _self for internal links, _blank for external.
     */
    function get_link_target(?string $url, string $default = '_self'): string {
        if (empty($url) || $url === '#' || str_starts_with($url, '/') || str_starts_with($url, 'mailto:') || str_starts_with($url, 'tel:')) {
            return $default;
        }
        
        $host = parse_url($url, PHP_URL_HOST);
        $appHost = request()->getHost();
        
        if ($host && $host !== $appHost) {
            return '_blank';
        }
        
        return $default;
    }
}

if (!function_exists('cdn_asset')) {
    /**
     * Get local asset if exists, otherwise fallback to CDN URL
     *
     * @param string $localPath Relative path to public folder
     * @param string $cdnUrl Fallback CDN URL
     * @return string
     */
    function cdn_asset(string $localPath, string $cdnUrl): string
    {
        $localPath = ltrim($localPath, '/');
        
        // 1. Check Laravel's configured public_path
        if (file_exists(public_path($localPath))) {
            return asset($localPath);
        }
        
        // 2. Check base_path('public/...') in case public_path() is bound to base_path() on shared hosting
        if (file_exists(base_path('public/' . $localPath))) {
            return asset($localPath);
        }

        // 3. Check base_path('...') directly (in case the index.php is in public_html root)
        if (file_exists(base_path($localPath))) {
            return asset($localPath);
        }
        
        return $cdnUrl;
    }
}

if (!function_exists('get_brand_svg')) {
    /**
     * Get the raw SVG markup for a brand icon or custom icon.
     *
     * @param string $name
     * @return string
     */
    function get_brand_svg(string $name): string
    {
        $name = strtolower(trim($name));
        // Remove prefixes if passed
        $name = str_replace(['fa-brands fa-', 'fab fa-', 'ki-'], '', $name);

        $svgs = [
            'facebook' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8.049c0-4.446-3.582-8.05-8-8.05C3.58 0-.002 3.603-.002 8.05c0 4.017 2.926 7.347 6.75 7.951v-5.625h-2.03V8.05H6.75V6.275c0-2.017 1.195-3.131 3.022-3.131.876 0 1.791.157 1.791.157v1.98h-1.009c-.993 0-1.303.621-1.303 1.258v1.51h2.218l-.354 2.326H9.25V16c3.824-.604 6.75-3.934 6.75-7.951"/></svg>',
            
            'youtube' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16"><path d="M8.051 1.999h-.089c-1.603-.02-6.72-.08-8.382.458-1.22.4-1.218 1.583-1.218 2.295a63 63 0 0 0-.012 8.154c-.012.711-.012 1.892 1.218 2.295 1.662.539 6.779.479 8.382.458.026 0 .053.004.079.004.026 0 .053-.004.079-.004 1.602-.021 6.721-.08 8.382-.458 1.22-.4 1.217-1.583 1.217-2.295.012-1.785.012-6.374 0-8.154-.013-.711-.013-1.892-1.217-2.295-1.66-.539-6.779-.479-8.382-.458h-.089zm-.584 9.098V5.086L12.5 8.092z"/></svg>',
            
            'github' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16"><path d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27s1.36.09 2 .27c1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.01 8.01 0 0 0 16 8c0-4.42-3.58-8-8-8"/></svg>',
            
            'twitter' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16"><path d="M12.6.75h2.454l-5.36 6.142L16 15.25h-4.937l-3.867-5.07-4.425 5.07H.316l5.733-6.57L0 .75h5.063l3.495 4.633L12.601.75Zm-.86 13.028h1.36L4.323 2.145H2.8655z"/></svg>',
            
            'instagram' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16"><path d="M8 0C5.829 0 5.556.01 4.703.048 3.85.088 3.269.222 2.76.42a3.9 3.9 0 0 0-1.417.923A3.9 3.9 0 0 0 .42 2.76C.222 3.268.087 3.85.048 4.7.01 5.555 0 5.827 0 8.001c0 2.172.01 2.444.048 3.297.04.852.174 1.433.372 1.942.205.526.478.972.923 1.417.444.445.89.719 1.416.923.51.198 1.09.333 1.942.372C5.555 15.99 5.827 16 8 16s2.444-.01 3.298-.048c.851-.04 1.434-.174 1.943-.372a3.9 3.9 0 0 0 1.416-.923c.445-.445.718-.891.923-1.417.197-.509.332-1.09.372-1.942C15.99 10.445 16 10.173 16 8s-.01-2.445-.048-3.299c-.04-.851-.175-1.433-.372-1.941a3.9 3.9 0 0 0-.923-1.417A3.9 3.9 0 0 0 13.24.42c-.51-.198-1.092-.333-1.943-.372C10.443.01 10.172 0 7.999 0zm-.08 1.44h.08c2.113 0 2.36.007 3.194.046.776.035 1.198.166 1.478.275a2.5 2.5 0 0 1 .92.599c.28.28.453.546.598.92.11.279.24.701.275 1.478.038.833.045 1.08.045 3.194 0 2.113-.007 2.36-.045 3.194-.036.777-.166 1.2-.275 1.478a2.5 2.5 0 0 1-.599.92c-.28.28-.546.453-.92.598-.278.11-.702.24-1.478.275-.833.038-1.08.045-3.194.045-2.113 0-2.36-.007-3.194-.046-.776-.035-1.198-.166-1.478-.275a2.5 2.5 0 0 1-.92-.599 2.5 2.5 0 0 1-.6-.92c-.11-.278-.24-.702-.275-1.478-.038-.833-.045-1.08-.045-3.194 0-2.113.007-2.36.045-3.194.036-.777.166-1.2.275-1.478.145-.374.318-.63.599-.92.28-.28.546-.453.92-.598.28-.11.702-.24 1.478-.275.833-.038 1.08-.045 3.194-.045zm0 2.56a4 4 0 1 0 0 8 4 4 0 0 0 0-8zm0 1.44a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5zm4.24-.86a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"/></svg>',
            
            'linkedin' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16"><path d="M0 1.146C0 .513.526 0 1.175 0h13.65C15.474 0 16 .513 16 1.146v13.708c0 .633-.526 1.146-1.175 1.146H1.175C.526 16 0 15.487 0 14.854zm4.943 12.248V6.169H2.542v7.225zm-1.2-8.212c.837 0 1.358-.554 1.358-1.248-.015-.709-.52-1.248-1.342-1.248S2.4 3.226 2.4 3.934c0 .694.521 1.248 1.327 1.248zm4.908 8.212V9.359c0-.216.016-.432.08-.586.173-.431.568-.878 1.232-.878.869 0 1.216.662 1.216 1.634v3.865h2.401V9.25c0-2.22-1.184-3.252-2.764-3.252-1.274 0-1.845.7-2.165 1.193v.025h-.016l.016-.025V6.169h-2.4c.03.678 0 7.225 0 7.225z"/></svg>',
            
            'tiktok' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16"><path d="M9 0h1.98c.144.715.54 1.617 1.235 2.512C12.895 3.38 13.84 4 15 4V6c-1.77 0-3.178-1-4-2.06V11.5a4.5 4.5 0 1 1-9-0.5C2 8.5 3.79 7 6 7V9c-1.103 0-2 .897-2 2a2.5 2.5 0 1 0 5 0V0z"/></svg>',
            
            'pinterest' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16"><path d="M8 0a8 8 0 0 0-2.915 15.452c-.07-.621-.132-1.574.027-2.253.144-.618.93-3.957.93-3.957s-.238-.475-.238-1.178c0-1.103.64-1.927 1.436-1.927.676 0 1.002.507 1.002 1.115 0 .68-.433 1.697-.656 2.637-.186.79.4 1.434 1.179 1.434 1.416 0 2.51-1.493 2.51-3.64 0-1.902-1.367-3.233-3.32-3.233-2.26 0-3.586 1.696-3.586 3.447 0 .683.263 1.415.592 1.814.065.079.075.148.055.228-.06.252-.196.8-.223.913-.036.15-.119.182-.275.11-1.026-.477-1.668-1.974-1.668-3.176 0-2.585 1.88-4.96 5.41-4.96 2.84 0 5.048 2.023 5.048 4.728 0 2.82-1.778 5.09-4.246 5.09-.829 0-1.61-.43-1.877-.94l-.51 1.94c-.184.708-.68 1.597-.98 2.086A8 8 0 1 0 8 0"/></svg>',
            
            'whatsapp' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16"><path d="M13.601 2.326A7.85 7.85 0 0 0 8 0a7.86 7.86 0 0 0-6.607 11.53L0 16l4.582-1.202a7.85 7.85 0 0 0 7.023.024L16 16l-1.202-4.367a7.86 7.86 0 0 0 1.203-3.607 7.85 7.85 0 0 0-2.402-5.701M8 14.43c-1.3 0-2.573-.35-3.682-1.01L4.05 13.24l-2.73.715.727-2.651-.18-.287a6.53 6.53 0 0 1-1.002-3.44c0-3.605 2.939-6.543 6.543-6.543 1.747 0 3.39.68 4.624 1.913a6.49 6.49 0 0 1 1.913 4.624c0 3.61-2.939 6.54-6.543 6.54M11.536 9.584c-.192-.096-1.136-.56-1.312-.624-.176-.064-.304-.096-.432.096-.128.192-.496.624-.608.752-.112.128-.224.144-.416.048a5.9 5.9 0 0 1-1.543-.952 6.2 6.2 0 0 1-1.07-1.332c-.113-.192-.012-.296.084-.392.088-.088.192-.224.288-.336.096-.112.128-.192.192-.32.064-.128.032-.24-.016-.336-.048-.096-.432-1.04-.592-1.424-.156-.379-.311-.328-.43-.334-.112-.006-.24-.006-.368-.006-.128 0-.336.048-.512.24-.176.192-.672.656-.672 1.6s.688 1.856.784 1.984c.096.128 1.352 2.064 3.279 2.896.459.198.816.316 1.096.406.46.146.879.125 1.21-.125.368-.28 1.136-.928 1.296-1.824.16-.896.16-1.664.112-1.824-.048-.16-.176-.224-.368-.32"/></svg>',
            
            'telegram' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M8.287 5.906q-1.168.486-4.666 2.01c-.56.223-.847.443-.86.661q-.02.327.414.498.43.17 1.247.437.947.308 1.293.308q.338 0 1.802-.99 1.464-.99 1.708-1.129.172-.097.291.01.12.107.014.21-.106.104-.989.923q-.883.818-.985.92-.102.102-.036.21.066.11 1.05.76.985.65 1.378.904.385.25.765.234.38-.016.58-.22.2-.204.3-.686l1.3-6.14q.1-.482-.1-.71-.2-.23-.625-.11L8.287 5.906"/></svg>',
            
            'dribbble' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16"><path d="M8 0a8 8 0 1 0 0 16A8 8 0 0 0 8 0M7.227 1.206c.712.44 1.347.962 1.9 1.55-1.137.953-2.484 1.825-4.015 2.593-.207-.585-.395-1.19-.562-1.813 1.037-.9 1.93-1.68 2.677-2.33M3.208 4.148q.244.887.585 1.737C2.083 6.5 1.248 6.643 1.207 6.65a7 7 0 0 1 2.001-2.502M1.164 7.647c.03-.007 1.047-.16 2.873-.787.21.46.438.92.684 1.37-1.92.632-3.447 1.97-3.528 2.046a7 7 0 0 1-.03-2.63m.967 3.398c.09-.08 1.637-1.39 3.585-2.003.224.417.464.823.722 1.217-2.155 1.045-3.321 2.809-3.364 2.877a7 7 0 0 1-.943-2.09m3.606 2.766q.081-.137.954-1.39c.27-.37.558-.75.864-1.13 1.32.748 2.41 1.764 3.197 2.977a7 7 0 0 1-5.015-.457m5.918-1.579c-.742-1.127-1.748-2.062-2.96-2.73.238-.344.492-.68.762-1.006 1.36.425 2.793.593 4.135.534-.08 1.26-.786 2.336-1.937 3.202M14.73 9.42c-1.328.058-2.693-.099-3.993-.506.27-.31.547-.63.827-.96 1.488.243 3.03.116 4.186-.062a7 7 0 0 1-1.02 1.528m.152-2.557c-1.157.17-2.614.287-4.04-.002a28 28 0 0 0-.756-1.03 26 26 0 0 0 3.755-1.922 7 7 0 0 1 1.041 2.954M12.784 2.21a28 28 0 0 1-3.645 1.847c-.524-.555-1.085-1.07-1.677-1.536a7 7 0 0 1 5.322-.31"/></svg>',
            
            'behance' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16"><path d="M4.654 3c.419 0 .821.045 1.202.134.381.09.706.224.974.403.268.18.473.418.616.716.143.298.215.657.215 1.077 0 .43-.075.8-.224 1.11a2.23 2.23 0 0 1-.61 7.828c-.287.213-.64.384-1.059.513A5.4 5.4 0 0 1 4.29 15H0V3h4.654zm-.281 5.344c.428 0 .807-.03 1.137-.09a1.72 1.72 0 0 0 .878-.44c.19-.17.285-.43.285-.78 0-.34-.09-.597-.272-.773a1.76 1.76 0 0 0-.83-.4c-.314-.077-.735-.115-1.264-.115H1.486v2.598h2.887zM1.487 13.5h3.044c.54 0 .983-.042 1.33-.128a1.75 1.75 0 0 0 .9-.504c.217-.23.326-.543.326-.94 0-.376-.104-.678-.31-.904a1.8 1.8 0 0 0-.82-.486 4.6 4.6 0 0 0-1.282-.128H1.487V13.5zm11.72-6.533c-.347 0-.666.05-.956.153A2.3 2.3 0 0 0 10.4 8.7c-.184.343-.277.766-.277 1.27 0 .522.096.963.287 1.323.19.36.467.636.83.829.362.192.788.288 1.278.288.423 0 .798-.063 1.127-.19.328-.126.598-.328.807-.604H15v.823h1V8.625h-1V9.22c-.156-.294-.41-.532-.76-.713-.352-.181-.767-.272-1.246-.272zm.238.96c.338 0 .62.069.845.207.225.138.384.336.478.592q.072.19.072.534v.538h-2.875c.03.352.115.63.257.835.14.205.324.353.551.446.228.092.493.138.796.138.452 0 .81-.1 1.077-.3a1.5 1.5 0 0 0 .49-.785h1c-.134.542-.455.972-.962 1.29-.508.318-1.12.477-1.836.477-.668 0-1.228-.125-1.68-.375a2.7 2.7 0 0 1-1.106-1.053c-.247-.456-.37-1.01-.37-1.666 0-.66.126-1.21.378-1.654a2.72 2.7 0 0 1 1.12-1.036c.463-.25.998-.375 1.605-.375zm-.21 2.348h1.76c-.02-.236-.086-.426-.197-.572a.86.86 0 0 0-.423-.3c-.154-.055-.333-.082-.538-.082-.233 0-.435.034-.604.103a.9.9 0 0 0-.4.322c-.116.146-.19.324-.222.534zm-.546-5.83h2.382v1H11.69v-1z"/></svg>',
            
            'envato' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16"><path d="M11.644 1.206c.712.44 1.347.962 1.9 1.55-1.137.953-2.484 1.825-4.015 2.593-.207-.585-.395-1.19-.562-1.813 1.037-.9 1.93-1.68 2.677-2.33M7.625 4.148c.244.887.585 1.737-1.71 1.737C4.5 6.5 3.665 6.643 3.624 6.65a7 7 0 0 1 2.001-2.502M5.58 7.647c.03-.007 1.047-.16 2.873-.787.21.46.438.92.684 1.37-1.92.632-3.447 1.97-3.528 2.046a7 7 0 0 1-.03-2.63m.967 3.398c.09-.08 1.637-1.39 3.585-2.003.224.417.464.823.722 1.217-2.155 1.045-3.321 2.809-3.364 2.877a7 7 0 0 1-.943-2.09m3.606 2.766q.081-.137.954-1.39c.27-.37.558-.75.864-1.13 1.32.748 2.41 1.764 3.197 2.977a7 7 0 0 1-5.015-.457m5.918-1.579c-.742-1.127-1.748-2.062-2.96-2.73.238-.344.492-.68.762-1.006 1.36.425 2.793.593 4.135.534-.08 1.26-.786 2.336-1.937 3.202"/></svg>'
        ];

        if (isset($svgs[$name])) {
            return $svgs[$name];
        }

        // Try querying the database for a custom icon if the name matches
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('custom_svg_icons')) {
                $customIcon = \Illuminate\Support\Facades\DB::table('custom_svg_icons')
                    ->where('name', $name)
                    ->first();
                if ($customIcon) {
                    return $customIcon->svg_code;
                }
            }
        } catch (\Throwable $e) {
            // silent
        }

        return '';
    }
}

if (!function_exists('show_execution_time_badge')) {
    function show_execution_time_badge(): bool
    {
        $settings = app(\App\Services\SettingsService::class);
        $enabledSetting = $settings->get('execution_time_badge_enabled', 'yes') === 'yes';

        return (bool) \App\Facades\Hook::applyFilters('theme.show_execution_time_badge', $enabledSetting);
    }
}

if (!function_exists('is_prefetch_request')) {
    function is_prefetch_request(): bool
    {
        $request = request();
        return $request->header('Purpose') === 'prefetch' ||
               $request->header('Sec-Purpose') === 'prefetch' ||
               $request->header('X-Purpose') === 'prefetch' ||
               ($request->headers->has('X-Moz') && $request->header('X-Moz') === 'prefetch');
    }
}

