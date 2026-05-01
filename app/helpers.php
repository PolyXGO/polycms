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

if (!function_exists('get_default_post_image')) {
    /**
     * Retrieve the global default post image URL.
     *
     * @param mixed $context Optional context (e.g. post object) for the filter.
     * @return string|null
     */
    function get_default_post_image(mixed $context = null): ?string
    {
        $default = get_option('reading_default_post_image', null, 'reading');
        return apply_filters('post.default_image', $default, $context);
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

            $html .= "<li class=\"{$itemClass}{$cssClass}" . ($hasChildren ? ' has-children' : '') . "\">";
            $html .= "<a href=\"" . e($url) . "\" class=\"{$linkClass}\"{$target}>" . e($item->title) . "</a>";

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

        // Check active state
        $isActive = false;
        if (isset($options['check_active']) && $options['check_active']) {
            $isActive = theme_is_menu_active($item);
        }
        $activeClass = $isActive ? ($options['active_class'] ?? 'active') : '';

        $html = "<{$wrapperTag} class=\"{$itemClass}{$cssClass} {$hasChildrenClass} {$activeClass}\">";
        $html .= "<a href=\"" . e($url) . "\" class=\"{$linkClass}\"{$target}>" . e($item->title) . "</a>";

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

        // 1. Exact match (normalized)
        if (rtrim($url, '/') === rtrim($currentUrl, '/') || rtrim($url, '/') === rtrim($fullUrl, '/')) {
            return true;
        }

        // 2. Intelligent Breadcrumb Match
        // If viewing a single post, highlight the Blog archive link
        if (request()->routeIs('posts.show')) {
            try {
                $blogUrl = route('posts.index');
                if (rtrim($url, '/') === rtrim($blogUrl, '/')) {
                    return true;
                }
            } catch (\Exception $e) {}
        }

        // If viewing a single product, highlight the Products archive link
        if (request()->routeIs('products.show')) {
            try {
                $shopUrl = route('products.index');
                if (rtrim($url, '/') === rtrim($shopUrl, '/')) {
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

        if (!str_contains($content, 'pricing-matrix-') && !str_contains($content, 'data-type="landing-block"') && !str_contains($content, '<!-- landing_block:')) {
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

        // 2. Handle modern placeholder format: <div data-type="landing-block" data-block-type="pricing_matrix" ...>
        $content = preg_replace_callback('/<div[^>]*data-type="landing-block"[^>]*data-block-type="pricing_matrix"[^>]*>.*?<\/div>/s', function ($matches) {
            $html = $matches[0];
            
            // Extract block data JSON
            $data = [];
            if (preg_match('/data-block-data=\'([^\']+)\'/', $html, $dataMatch)) {
                $data = json_decode($dataMatch[1], true) ?? [];
            } elseif (preg_match('/data-block-data="([^"]+)"/', $html, $dataMatch)) {
                $data = json_decode(htmlspecialchars_decode($dataMatch[1]), true) ?? [];
            }
            
            // We need a product.
            $product = request()->route('product'); 
            if (!$product) {
                // Try choosing any referenced product id if available in data
                if (isset($data['product_id'])) {
                    $product = \App\Models\Product::find($data['product_id']);
                }
            }
            
            if (!$product) {
                return $html;
            }
            
            try {
                return view('theme::blocks.pricing', [
                    'product' => $product,
                    'attrs' => $data
                ])->render();
            } catch (\Exception $e) {
                return $html;
            }
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

        return $content;
    }
}
