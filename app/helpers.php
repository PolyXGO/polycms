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
     * @param string|null $locale Optional locale override
     * @return string Translated text
     */
    function _l(string $text, ?string $locale = null): string
    {
        return \App\Helpers\LanguageHelper::translate($text, $locale);
    }
}

if (!function_exists('_e')) {
    /**
     * Global helper function _e() - Similar to WordPress _e()
     *
     * @param string $text The text to translate and echo
     * @param string|null $locale Optional locale override
     */
    function _e(string $text, ?string $locale = null): void
    {
        \App\Helpers\LanguageHelper::echo($text, $locale);
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
