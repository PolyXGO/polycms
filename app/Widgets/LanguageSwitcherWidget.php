<?php

declare(strict_types=1);

namespace App\Widgets;

use App\Helpers\LanguageHelper;
use App\Models\WidgetInstance;

class LanguageSwitcherWidget
{
    public function render(WidgetInstance $instance): string
    {
        $config = $instance->config ?? [];
        $displayStyle = $config['display_style'] ?? 'list';
        $showFlags = (bool) ($config['show_flags'] ?? false);

        $languages = $this->getAvailableLanguages();

        if (empty($languages)) {
            return '';
        }

        $title = $instance->title ?: _l('Languages');
        $currentLang = LanguageHelper::getCurrentLanguage();

        $html = '<div class="widget widget-language-switcher">';
        $html .= '<h3 class="widget-title">' . e($title) . '</h3>';

        if ($displayStyle === 'dropdown') {
            $html .= $this->renderDropdown($languages, $currentLang);
        } else {
            $html .= $this->renderList($languages, $currentLang, $showFlags);
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * @return array<string, string>
     */
    protected function getAvailableLanguages(): array
    {
        $languages = [];
        
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('languages')) {
                $activeLanguages = \App\Models\Language::where('is_active', true)->orderBy('sort_order', 'asc')->get();
                foreach ($activeLanguages as $lang) {
                    $languages[$lang->code] = $lang->native_name ?? $lang->name;
                }
            }
        } catch (\Exception $e) {
            // Fallback if table doesn't exist or database not available
        }

        // Fallback to directory scan if database yields no languages
        if (empty($languages)) {
            $default = config('app.locale', 'en');
            $languages[$default] = $this->resolveLanguageName($default);

            $langPath = base_path('lang');
            if (is_dir($langPath)) {
                foreach (glob($langPath . '/*') as $entry) {
                    if (is_dir($entry)) {
                        $code = basename($entry);
                    } elseif (is_file($entry) && str_ends_with($entry, '.php')) {
                        $code = basename($entry, '.php');
                    } elseif (is_file($entry) && str_ends_with($entry, '.json')) {
                        $code = basename($entry, '.json');
                    } else {
                        continue;
                    }

                    if (!isset($languages[$code])) {
                        $languages[$code] = $this->resolveLanguageName($code);
                    }
                }
            }

            ksort($languages);
        }

        return $languages;
    }

    protected function resolveLanguageName(string $code): string
    {
        $code = strtolower($code);

        if (class_exists(\Locale::class)) {
            return ucfirst(\Locale::getDisplayLanguage($code, $code) ?: $code);
        }

        return ucfirst($code);
    }

    /**
     * @param array<string, string> $languages
     */
    protected function renderList(array $languages, string $currentLang, bool $showFlags): string
    {
        $html = '<ul class="widget-language-list">';

        foreach ($languages as $code => $name) {
            $isActive = $code === $currentLang;
            $class = $isActive ? ' class="active"' : '';
            $url = $this->buildLanguageUrl($code);

            $html .= '<li' . $class . '>';
            $html .= '<a href="' . e($url) . '" data-lang="' . e($code) . '">';

            if ($showFlags) {
                $html .= '<span class="flag flag-' . e(strtolower($code)) . '"></span> ';
            }

            $html .= e($name) . '</a>';
            $html .= '</li>';
        }

        $html .= '</ul>';

        return $html;
    }

    /**
     * @param array<string, string> $languages
     */
    protected function renderDropdown(array $languages, string $currentLang): string
    {
        $html = '<form method="get" action="' . e(request()->url()) . '" class="widget-language-dropdown">';
        $html .= '<select name="lang" onchange="this.form.submit()">';

        foreach ($languages as $code => $name) {
            $selected = $code === $currentLang ? ' selected' : '';
            $html .= '<option value="' . e($code) . '"' . $selected . '>' . e($name) . '</option>';
        }

        $html .= '</select>';

        foreach ($this->preserveQueryParameters(['lang']) as $key => $value) {
            $html .= '<input type="hidden" name="' . e($key) . '" value="' . e($value) . '">';
        }

        $html .= '</form>';

        return $html;
    }

    protected function buildLanguageUrl(string $lang): string
    {
        $query = $this->preserveQueryParameters();
        $query['lang'] = $lang;

        $queryString = http_build_query($query);

        return $queryString === '' ? request()->url() : request()->url() . '?' . $queryString;
    }

    /**
     * @param array<int, string> $except
     * @return array<string, string>
     */
    protected function preserveQueryParameters(array $except = []): array
    {
        $query = request()->query();

        foreach ($except as $key) {
            unset($query[$key]);
        }

        return array_map('strval', $query);
    }
}

