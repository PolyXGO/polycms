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
        $showLabel = (bool) ($config['show_label'] ?? true);

        // Fallback: if show_label is false, we must show the flag!
        if (!$showLabel) {
            $showFlags = true;
        }

        $languages = $this->getAvailableLanguages();

        if (empty($languages)) {
            return '';
        }

        $title = $instance->title ?: _l('Languages');
        $currentLang = LanguageHelper::getCurrentLanguage();

        $html = '<div class="widget widget-language-switcher">';
        $html .= '<h3 class="widget-title">' . e($title) . '</h3>';

        if ($displayStyle === 'dropdown') {
            $html .= $this->renderDropdown($languages, $currentLang, $showFlags, $showLabel);
        } else {
            $html .= $this->renderList($languages, $currentLang, $showFlags, $showLabel);
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
    protected function renderList(array $languages, string $currentLang, bool $showFlags, bool $showLabel): string
    {
        $html = '<ul class="widget-language-list">';

        foreach ($languages as $code => $name) {
            $isActive = $code === $currentLang;
            $class = $isActive ? ' class="active"' : '';
            $url = $this->buildLanguageUrl($code);

            $html .= '<li' . $class . '>';
            $html .= '<a href="' . e($url) . '" data-lang="' . e($code) . '">';

            if ($showFlags) {
                $html .= '<span class="flag-icon-wrapper" style="display: inline-flex; align-items: center; margin-right: 8px; vertical-align: middle;">' . LanguageHelper::getFlagSvg($code) . '</span>';
            }

            if ($showLabel) {
                $html .= '<span class="language-name">' . e($name) . '</span>';
            } else {
                $html .= '<span class="language-name sr-only" style="position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); border: 0;">' . e($name) . '</span>';
            }

            $html .= '</a>';
            $html .= '</li>';
        }

        $html .= '</ul>';

        return $html;
    }

    /**
     * @param array<string, string> $languages
     */
    protected function renderDropdown(array $languages, string $currentLang, bool $showFlags, bool $showLabel): string
    {
        $activeName = $languages[$currentLang] ?? $this->resolveLanguageName($currentLang);
        $activeFlag = LanguageHelper::getFlagSvg($currentLang);

        $html = '<div class="widget-language-dropdown-custom">';
        $html .= '<div class="widget-language-dropdown-trigger">';
        
        if ($showFlags) {
            $html .= '<span class="flag-icon-wrapper" style="display: inline-flex; align-items: center; vertical-align: middle;">' . $activeFlag . '</span>';
        }
        
        if ($showLabel) {
            $marginLeft = $showFlags ? ' style="margin-left: 6px;"' : '';
            $html .= '<span class="active-label"' . $marginLeft . '>' . e($activeName) . '</span>';
        }
        
        $html .= '<svg class="widget-language-dropdown-arrow" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-left: 6px; transition: transform 0.2s;"><path d="m6 9 6 6 6-6"/></svg>';
        $html .= '</div>';
        
        $html .= '<ul class="widget-language-dropdown-menu">';
        foreach ($languages as $code => $name) {
            $isActive = $code === $currentLang;
            $activeClass = $isActive ? ' active' : '';
            $url = $this->buildLanguageUrl($code);
            $itemFlag = LanguageHelper::getFlagSvg($code);

            $html .= '<li class="' . $activeClass . '">';
            $html .= '<a href="' . e($url) . '" data-lang="' . e($code) . '">';
            $html .= '<span class="flag-icon-wrapper" style="display: inline-flex; align-items: center; margin-right: 8px; vertical-align: middle;">' . $itemFlag . '</span>';
            $html .= '<span class="language-name">' . e($name) . '</span>';
            $html .= '</a>';
            $html .= '</li>';
        }
        $html .= '</ul>';
        $html .= '</div>';

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

