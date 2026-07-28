<?php

declare(strict_types=1);

namespace App\Widgets;

use App\Models\WidgetInstance;

class BlogSearchWidget
{
    protected static bool $assetsRendered = false;

    public function render(WidgetInstance $instance): string
    {
        $config = $instance->config ?? [];

        // Auto-pull custom border configuration if this is a menu item
        $instanceId = '';
        if (method_exists($instance, 'getAttributes')) {
            $instanceId = (string) ($instance->getAttributes()['id'] ?? $instance->id);
        } else {
            $instanceId = (string) ($instance->id ?? '');
        }

        if (str_starts_with($instanceId, 'menu-item-')) {
            $itemId = (int) substr($instanceId, 10);
            $menuItem = \App\Models\MenuItem::find($itemId);
            if ($menuItem && $menuItem->type === 'search') {
                $config['border_color'] = $config['border_color'] ?? $menuItem->search_border_color;
                $config['border_width'] = $config['border_width'] ?? $menuItem->search_border_width;
                $config['border_radius'] = $config['border_radius'] ?? $menuItem->search_border_radius;
                $config['bg_color'] = $config['bg_color'] ?? $menuItem->search_bg_color;
                $config['bg_hover_color'] = $config['bg_hover_color'] ?? $menuItem->search_bg_hover_color;
            }
        }

        $title = $instance->title ?: _l('Search');
        $style = $this->normalizeStyle((string) ($config['display_style'] ?? 'form'));
        $triggerVariant = $this->normalizeTriggerVariant((string) ($config['trigger_variant'] ?? 'button'));
        $placeholder = (string) ($config['placeholder'] ?? _l('Search blog...'));
        $buttonLabel = (string) ($config['button_label'] ?? _l('Search'));
        $showTitle = $this->boolValue($config['show_title'] ?? true);
        $suggestionsEnabled = $this->boolValue($config['suggestions_enabled'] ?? true);
        $suggestionScope = $this->normalizeSuggestionScope((string) ($config['suggestion_scope'] ?? 'posts'));
        $suggestionLimit = max(1, min((int) ($config['suggestion_limit'] ?? 6), 10));
        $action = $this->resolveActionUrl((string) ($config['search_target'] ?? 'posts'));
        $suggestionsUrl = $this->resolveSuggestionsUrl();
        $id = 'widget-search-' . $instance->id;

        $borderColor = (string) ($config['border_color'] ?? '');
        $borderWidth = (string) ($config['border_width'] ?? '');
        $borderRadius = (string) ($config['border_radius'] ?? '');
        $bgColor = (string) ($config['bg_color'] ?? '');
        $bgHoverColor = (string) ($config['bg_hover_color'] ?? '');

        $customStyles = [];
        $hoverStyles = [];
        if ($borderColor !== '') {
            $customStyles[] = 'border-color: ' . $borderColor . ' !important;';
        }
        if ($borderWidth !== '') {
            $customStyles[] = 'border-width: ' . $borderWidth . ' !important;';
            if ($borderWidth === '0px' || $borderWidth === '0') {
                $customStyles[] = 'border-style: none !important;';
            } else {
                $customStyles[] = 'border-style: solid !important;';
            }
        }
        if ($borderRadius !== '') {
            $customStyles[] = 'border-radius: ' . $borderRadius . ' !important;';
        }
        if ($bgColor !== '') {
            $customStyles[] = 'background-color: ' . $bgColor . ' !important;';
            $customStyles[] = 'background-image: none !important;';
        }
        if ($bgHoverColor !== '') {
            $hoverStyles[] = 'background-color: ' . $bgHoverColor . ' !important;';
            $hoverStyles[] = 'background-image: none !important;';
        }

        $cssStyles = '';
        if (!empty($customStyles) || !empty($hoverStyles)) {
            $targetSelector = in_array($style, ['icon_modal', 'icon_expand'], true)
                ? '#' . $id . ' .widget-search-trigger'
                : '#' . $id . ' .widget-search-form';
            if (!empty($customStyles)) {
                $cssStyles .= $targetSelector . ' { ' . implode(' ', $customStyles) . ' } ';
            }
            if (!empty($hoverStyles)) {
                $cssStyles .= $targetSelector . ':hover { ' . implode(' ', $hoverStyles) . ' } ';
            }
        }

        $html = '';
        if ($cssStyles !== '') {
            $html .= '<style>' . $cssStyles . '</style>';
        }

        $html .= '<div id="' . e($id) . '" class="widget widget-blog-search widget-blog-search--' . e($style) . '"'
            . ' data-search-widget'
            . ' data-search-style="' . e($style) . '"'
            . ' data-search-locale="' . e((string) app()->getLocale()) . '"'
            . ' data-suggestions-enabled="' . ($suggestionsEnabled ? '1' : '0') . '"'
            . ' data-suggestions-url="' . e($suggestionsUrl) . '"'
            . ' data-suggestion-scope="' . e($suggestionScope) . '"'
            . ' data-suggestion-limit="' . e((string) $suggestionLimit) . '">';

        if ($showTitle && !in_array($style, ['icon_modal', 'icon_expand'], true)) {
            $html .= '<h3 class="widget-title">' . e($title) . '</h3>';
        }

        $html .= match ($style) {
            'icon_modal' => $this->renderModalSearch($id, $title, $action, $placeholder, $buttonLabel, $triggerVariant),
            'icon_expand' => $this->renderExpandableSearch($id, $title, $action, $placeholder, $buttonLabel, $triggerVariant),
            'icon_inline', 'pill', 'minimal' => $this->renderInlineSearch($id, $action, $placeholder, $buttonLabel, $style),
            default => $this->renderFullForm($id, $action, $placeholder, $buttonLabel),
        };

        $html .= '</div>';
        $html .= $this->renderAssetsOnce();

        return $html;
    }

    protected function renderFullForm(string $id, string $action, string $placeholder, string $buttonLabel): string
    {
        return '<form action="' . e($action) . '" method="get" class="widget-search-form widget-search-form--default" data-widget-search-form>'
            . $this->renderInput($id, $placeholder)
            . '<button type="submit" class="widget-search-submit-button">' . e($buttonLabel) . '</button>'
            . $this->renderSuggestionsBox()
            . '</form>';
    }

    protected function renderInlineSearch(string $id, string $action, string $placeholder, string $buttonLabel, string $style): string
    {
        return '<form action="' . e($action) . '" method="get" class="widget-search-form widget-search-form--' . e($style) . '" data-widget-search-form>'
            . '<span class="widget-search-leading-icon" aria-hidden="true">' . $this->searchIcon() . '</span>'
            . $this->renderInput($id, $placeholder)
            . '<button type="submit" class="widget-search-icon-submit" aria-label="' . e($buttonLabel) . '">' . $this->searchIcon() . '</button>'
            . $this->renderSuggestionsBox()
            . '</form>';
    }

    protected function renderModalSearch(string $id, string $title, string $action, string $placeholder, string $buttonLabel, string $triggerVariant): string
    {
        $triggerClass = 'widget-search-trigger' . ($triggerVariant === 'icon' ? ' widget-search-trigger--icon' : '');

        return '<button type="button" class="' . e($triggerClass) . '" data-search-open aria-label="' . e($title) . '">'
            . $this->searchIcon()
            . '</button>'
            . '<div class="widget-search-modal" data-search-modal hidden>'
            . '<div class="widget-search-modal__backdrop" aria-hidden="true"></div>'
            . '<div class="widget-search-modal__panel" role="dialog" aria-modal="true" aria-labelledby="' . e($id) . '-title">'
            . '<div class="widget-search-modal__header">'
            . '<h3 id="' . e($id) . '-title">' . e($title) . '</h3>'
            . '<button type="button" class="widget-search-modal__close" data-search-close aria-label="' . e(_l('Close')) . '">×</button>'
            . '</div>'
            . '<form action="' . e($action) . '" method="get" class="widget-search-form widget-search-form--modal" data-widget-search-form>'
            . $this->renderInput($id, $placeholder)
            . '<button type="submit" class="widget-search-icon-submit" aria-label="' . e($buttonLabel) . '">' . $this->searchIcon() . '</button>'
            . $this->renderSuggestionsBox()
            . '</form>'
            . '</div>'
            . '</div>';
    }

    protected function renderExpandableSearch(string $id, string $title, string $action, string $placeholder, string $buttonLabel, string $triggerVariant): string
    {
        $triggerClass = 'widget-search-trigger' . ($triggerVariant === 'icon' ? ' widget-search-trigger--icon' : '');

        return '<div class="widget-search-expand">'
            . '<button type="button" class="' . e($triggerClass) . '" data-search-open aria-label="' . e($title) . '">'
            . $this->searchIcon()
            . '</button>'
            . '<div class="widget-search-expand__panel" data-search-panel hidden>'
            . '<form action="' . e($action) . '" method="get" class="widget-search-form widget-search-form--expand" data-widget-search-form>'
            . '<span class="widget-search-leading-icon" aria-hidden="true">' . $this->searchIcon() . '</span>'
            . $this->renderInput($id, $placeholder)
            . '<button type="submit" class="widget-search-icon-submit" aria-label="' . e($buttonLabel) . '">' . $this->searchIcon() . '</button>'
            . $this->renderSuggestionsBox()
            . '</form>'
            . '</div>'
            . '</div>';
    }

    protected function renderInput(string $id, string $placeholder): string
    {
        return '<label class="sr-only" for="' . e($id) . '">' . e(_l('Search')) . '</label>'
            . '<input id="' . e($id) . '" type="text" inputmode="search" enterkeyhint="search" name="search" autocomplete="off" spellcheck="false" placeholder="' . e($placeholder) . '" data-search-input />';
    }

    protected function renderSuggestionsBox(): string
    {
        return '<div class="widget-search-suggestions" data-search-suggestions hidden></div>';
    }

    protected function normalizeStyle(string $style): string
    {
        return in_array($style, ['form', 'icon_modal', 'icon_expand', 'icon_inline', 'pill', 'minimal'], true)
            ? $style
            : 'form';
    }

    protected function normalizeTriggerVariant(string $variant): string
    {
        return in_array($variant, ['button', 'icon'], true) ? $variant : 'button';
    }

    protected function normalizeSuggestionScope(string $scope): string
    {
        return in_array($scope, ['posts', 'pages', 'products', 'all'], true) ? $scope : 'posts';
    }

    protected function boolValue(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_string($value)) {
            return !in_array(strtolower($value), ['0', 'false', 'off', 'no'], true);
        }

        return (bool) $value;
    }

    protected function resolveActionUrl(string $target): string
    {
        if ($target === 'products') {
            return theme_permalink_url('products', '', 'archive');
        }

        return theme_permalink_url('posts', '', 'archive');
    }

    protected function resolveSuggestionsUrl(): string
    {
        try {
            return route('api.v1.search.suggestions');
        } catch (\Throwable) {
            return url('/api/v1/search/suggestions');
        }
    }

    protected function searchIcon(): string
    {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.35-4.35"></path></svg>';
    }

    protected function renderAssetsOnce(): string
    {
        if (self::$assetsRendered) {
            return '';
        }

        self::$assetsRendered = true;

        return <<<'HTML'
<style>
body.polycms-search-widget-open { overflow: hidden; }

.widget-blog-search {
    position: relative;
}

.widget-blog-search--icon_modal,
.widget-blog-search--icon_expand,
.widget-blog-search--icon_inline,
.widget-blog-search--pill,
.widget-blog-search--minimal {
    display: inline-flex;
    align-items: center;
}

.widget-search-trigger,
.widget-search-icon-submit {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2.25rem;
    height: 2.25rem;
    border-radius: 9999px;
    border: 1px solid rgba(148, 163, 184, 0.28);
    background: #f8fafc;
    color: #475569;
    cursor: pointer;
    box-shadow: none;
    transition: all 0.2s ease;
    line-height: 0;
}

.widget-search-trigger--icon {
    width: auto;
    height: auto;
    padding: 0.35rem;
    border: 0;
    background: transparent;
    color: inherit;
    box-shadow: none;
}

.widget-search-trigger--icon:hover {
    background: transparent;
    border-color: transparent;
    color: #2563eb;
}

html.dark .widget-search-trigger--icon,
.dark .widget-search-trigger--icon {
    background: transparent;
    border: 0;
    color: #cbd5e1;
}

html.dark .widget-search-trigger--icon:hover,
.dark .widget-search-trigger--icon:hover {
    background: transparent;
    border-color: transparent;
    color: #eff6ff;
}

.widget-search-trigger:not(.widget-search-trigger--icon):hover,
.widget-search-icon-submit:hover {
    background: #eef2ff;
    border-color: rgba(59, 130, 246, 0.35);
    color: #2563eb;
}

html.dark .widget-search-trigger,
html.dark .widget-search-icon-submit,
.dark .widget-search-trigger,
.dark .widget-search-icon-submit {
    background: #1e293b;
    border-color: rgba(51, 65, 85, 0.9);
    color: #cbd5e1;
}

html.dark .widget-search-trigger:not(.widget-search-trigger--icon):hover,
html.dark .widget-search-icon-submit:hover,
.dark .widget-search-trigger:not(.widget-search-trigger--icon):hover,
.dark .widget-search-icon-submit:hover {
    background: #334155;
    border-color: rgba(96, 165, 250, 0.45);
    color: #eff6ff;
}

.widget-search-trigger svg,
.widget-search-icon-submit svg {
    width: 1rem;
    height: 1rem;
}

.widget-search-modal[hidden],
.widget-search-expand__panel[hidden],
.widget-search-suggestions[hidden] {
    display: none !important;
}

.widget-search-modal {
    position: fixed;
    inset: 0;
    z-index: 120;
    display: flex;
    align-items: flex-start;
    justify-content: center;
    padding: 12vh 1rem 1rem;
}

.widget-search-modal__backdrop {
    position: absolute;
    inset: 0;
    background: rgba(15, 23, 42, 0.45);
    backdrop-filter: blur(6px);
}

.widget-search-modal__panel {
    position: relative;
    width: min(42rem, 100%);
    border: 1px solid rgba(148, 163, 184, 0.22);
    border-radius: 1rem;
    background: #ffffff;
    color: #0f172a;
    box-shadow: 0 24px 80px rgba(15, 23, 42, 0.18);
    padding: 1.25rem;
}

html.dark .widget-search-modal__panel,
.dark .widget-search-modal__panel {
    background: #0f172a;
    color: #e2e8f0;
    border-color: rgba(51, 65, 85, 0.95);
}

.widget-search-modal__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1rem;
}

.widget-search-modal__header h3 {
    margin: 0;
    font-size: 1.05rem;
    font-weight: 700;
}

.widget-search-modal__close {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2rem;
    height: 2rem;
    border-radius: 9999px;
    border: 1px solid rgba(148, 163, 184, 0.35);
    background: #f8fafc;
    color: #475569;
    cursor: pointer;
    line-height: 0;
}

html.dark .widget-search-modal__close,
.dark .widget-search-modal__close {
    background: #1e293b;
    color: #cbd5e1;
    border-color: rgba(51, 65, 85, 0.9);
}

html.dark .widget-blog-search .widget-search-form--modal .widget-search-icon-submit,
.dark .widget-blog-search .widget-search-form--modal .widget-search-icon-submit {
    background: #1e293b !important;
    color: #cbd5e1 !important;
    border-color: rgba(51, 65, 85, 0.9) !important;
}

.widget-search-form {
    position: relative;
}

.widget-search-form--default {
    display: flex;
    margin-top: 0.5rem;
}

.widget-search-form--modal,
.widget-search-form--expand,
.widget-search-form--icon_inline,
.widget-search-form--pill,
.widget-search-form--minimal {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin: 0;
}

.widget-search-form input[type="search"],
.widget-search-form input[type="text"] {
    flex: 1;
    width: 100%;
    border: 0;
    outline: 0;
    box-shadow: none;
    background: transparent;
    color: inherit;
    font: inherit;
    min-width: 0;
}

.widget-search-form--default input[type="search"],
.widget-search-form--default input[type="text"] {
    border: 1px solid rgba(148, 163, 184, 0.28);
    border-right: none;
    border-radius: 0.75rem 0 0 0.75rem;
    background: #ffffff;
    padding: 0.625rem 1rem;
}

.widget-search-form--modal {
    padding: 0.25rem 0.4rem 0.25rem 0.85rem;
    border: 1px solid rgba(148, 163, 184, 0.28);
    border-radius: 9999px;
    background: #ffffff;
}

html.dark .widget-search-form--modal,
html.dark .widget-search-form--expand,
html.dark .widget-search-form--icon_inline,
html.dark .widget-search-form--pill,
.dark .widget-search-form--modal,
.dark .widget-search-form--expand,
.dark .widget-search-form--icon_inline,
.dark .widget-search-form--pill {
    background: #111827;
    border-color: rgba(51, 65, 85, 0.9);
    color: #e2e8f0;
    box-shadow: 0 18px 50px rgba(0, 0, 0, 0.35);
}

html.dark .widget-search-form input::placeholder,
.dark .widget-search-form input::placeholder {
    color: #94a3b8;
}

.widget-blog-search .widget-search-form--modal .widget-search-icon-submit,
.widget-blog-search .widget-search-form--expand .widget-search-icon-submit,
.widget-blog-search .widget-search-form--icon_inline .widget-search-icon-submit,
.widget-blog-search .widget-search-form--pill .widget-search-icon-submit {
    flex: 0 0 auto;
    margin-left: auto;
    padding: 0 !important;
    width: 2.25rem !important;
    height: 2.25rem !important;
    min-width: 2.25rem !important;
    min-height: 2.25rem !important;
    line-height: 0 !important;
    appearance: none !important;
    -webkit-appearance: none !important;
}

.widget-blog-search .widget-search-form--modal .widget-search-icon-submit {
    background: #f8fafc !important;
    color: #475569 !important;
    border: 1px solid rgba(148, 163, 184, 0.35) !important;
}

.widget-blog-search .widget-search-form--expand .widget-search-icon-submit,
.widget-blog-search .widget-search-form--icon_inline .widget-search-icon-submit,
.widget-blog-search .widget-search-form--pill .widget-search-icon-submit {
    background: transparent !important;
    color: inherit !important;
    border: 0 !important;
    box-shadow: none !important;
}

.widget-search-form--expand {
    position: relative;
    padding: 0.25rem 0.4rem 0.25rem 0.85rem;
    border: 1px solid rgba(148, 163, 184, 0.28);
    border-radius: 9999px;
    background: #ffffff;
}

.widget-search-form--expand input[type="search"],
.widget-search-form--expand input[type="text"],
.widget-search-form--icon_inline input[type="search"],
.widget-search-form--icon_inline input[type="text"],
.widget-search-form--pill input[type="search"],
.widget-search-form--pill input[type="text"],
.widget-search-form--minimal input[type="search"],
.widget-search-form--minimal input[type="text"] {
    padding: 0.5rem 0.25rem;
    min-width: 11rem;
}

.widget-search-form--icon_inline,
.widget-search-form--pill,
.widget-search-form--expand {
    border: 1px solid rgba(148, 163, 184, 0.28);
    border-radius: 9999px;
    background: #ffffff;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
    padding: 0.25rem 0.375rem 0.25rem 0.875rem;
}

.widget-search-form--minimal {
    border-bottom: 1px solid rgba(148, 163, 184, 0.28);
    padding-bottom: 0.25rem;
}

.widget-search-suggestions {
    position: absolute;
    top: calc(100% + 0.5rem);
    left: 0;
    right: 0;
    z-index: 100;
    max-height: 22rem;
    overflow-y: auto;
    border: 1px solid rgba(148, 163, 184, 0.25);
    border-radius: 0.9rem;
    background: #ffffff;
    box-shadow: 0 18px 50px rgba(15, 23, 42, 0.14);
    padding: 0.35rem;
}

html.dark .widget-search-suggestions,
.dark .widget-search-suggestions {
    background: #0f172a;
    border-color: rgba(51, 65, 85, 0.95);
    box-shadow: 0 22px 60px rgba(0, 0, 0, 0.42);
    color: #e2e8f0;
}

.widget-search-suggestion {
    display: grid;
    gap: 0.35rem;
    padding: 0.9rem 1rem;
    border-radius: 0.75rem;
    color: inherit;
    text-decoration: none;
}

.widget-search-suggestion:hover {
    background: #f8fafc;
}

html.dark .widget-search-suggestion:hover,
.dark .widget-search-suggestion:hover {
    background: rgba(30, 41, 59, 0.85);
}

.widget-search-suggestion__title {
    font-size: 0.95rem;
    font-weight: 600;
    line-height: 1.35;
    color: #0f172a;
}

html.dark .widget-search-suggestion__title,
.dark .widget-search-suggestion__title {
    color: #f8fafc;
}

.widget-search-suggestion__meta,
.widget-search-suggestion__excerpt,
.widget-search-suggestion-empty {
    font-size: 0.8rem;
    line-height: 1.45;
    color: #64748b;
}

.widget-search-suggestion__meta {
    margin-top: -0.1rem;
}

html.dark .widget-search-suggestion__meta,
html.dark .widget-search-suggestion__excerpt,
html.dark .widget-search-suggestion-empty,
.dark .widget-search-suggestion__meta,
.dark .widget-search-suggestion__excerpt,
.dark .widget-search-suggestion-empty {
    color: #94a3b8;
}
</style>
<script>
(function() {
    if (window.PolyCMSSearchWidgetReady) return;
    window.PolyCMSSearchWidgetReady = true;

    function initSearchWidget(root) {
        if (!root || root.__polySearchReady) return;
        root.__polySearchReady = true;

        var openers = root.querySelectorAll('[data-search-open]');
        var closers = root.querySelectorAll('[data-search-close]');
        var modal = root.querySelector('[data-search-modal]');
        var panel = root.querySelector('[data-search-panel]');
        var forms = root.querySelectorAll('[data-widget-search-form]');
        var suggestionsEnabled = root.dataset.suggestionsEnabled === '1';
        var suggestionsUrl = root.dataset.suggestionsUrl || '/api/v1/search/suggestions';
        var suggestionScope = root.dataset.suggestionScope || 'posts';
        var suggestionLimit = root.dataset.suggestionLimit || '6';
        var searchLocale = root.dataset.searchLocale || document.documentElement.lang || 'en';
        var requestId = 0;

        if (modal && modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }

        function getSearchInput() {
            if (modal) {
                var modalInput = modal.querySelector('[data-search-input]');
                if (modalInput) return modalInput;
            }
            return root.querySelector('[data-search-input]');
        }

        function open() {
            root.classList.add('is-open');
            document.body.classList.add('polycms-search-widget-open');
            if (modal) modal.hidden = false;
            if (panel) panel.hidden = false;
            var input = getSearchInput();
            if (input) setTimeout(function() { input.focus(); }, 30);
        }

        function close() {
            root.classList.remove('is-open');
            document.body.classList.remove('polycms-search-widget-open');
            if (modal) modal.hidden = true;
            if (panel) panel.hidden = true;
            hideSuggestions();
        }

        function hideSuggestions() {
            var suggestionBoxes = modal
                ? modal.querySelectorAll('[data-search-suggestions]')
                : root.querySelectorAll('[data-search-suggestions]');
            suggestionBoxes.forEach(function(box) {
                box.hidden = true;
                box.innerHTML = '';
            });
        }

        function renderSuggestions(box, results) {
            if (!box) return;
            if (!results.length) {
                box.innerHTML = '<div class="widget-search-suggestion-empty">No results found</div>';
                box.hidden = false;
                return;
            }

            box.innerHTML = results.map(function(item) {
                var title = escapeHtml(item.title || '');
                var subtitle = escapeHtml(item.subtitle || item.type || '');
                var excerpt = escapeHtml(item.excerpt || '');
                var url = escapeAttr(item.url || '#');
                return '<a class="widget-search-suggestion" href="' + url + '">'
                    + '<span class="widget-search-suggestion__title">' + title + '</span>'
                    + (subtitle ? '<span class="widget-search-suggestion__meta">' + subtitle + '</span>' : '')
                    + (excerpt ? '<span class="widget-search-suggestion__excerpt">' + excerpt + '</span>' : '')
                    + '</a>';
            }).join('');
            box.hidden = false;
        }

        function fetchSuggestions(input, box) {
            if (!suggestionsEnabled || !input || !box) return;
            var query = input.value.trim();
            if (query.length < 2) {
                box.hidden = true;
                box.innerHTML = '';
                return;
            }

            var currentRequest = ++requestId;
            var url = new URL(suggestionsUrl, window.location.origin);
            url.searchParams.set('q', query);
            url.searchParams.set('scope', suggestionScope);
            url.searchParams.set('limit', suggestionLimit);
            url.searchParams.set('locale', searchLocale);

            fetch(url.toString(), { headers: { 'Accept': 'application/json' } })
                .then(function(response) { return response.ok ? response.json() : { results: [] }; })
                .then(function(payload) {
                    if (currentRequest !== requestId) return;
                    renderSuggestions(box, Array.isArray(payload.results) ? payload.results : []);
                })
                .catch(function() {
                    if (currentRequest === requestId) hideSuggestions();
                });
        }

        function debounce(fn, delay) {
            var timer = null;
            return function() {
                var args = arguments;
                clearTimeout(timer);
                timer = setTimeout(function() { fn.apply(null, args); }, delay);
            };
        }

        openers.forEach(function(button) {
            button.addEventListener('click', function(event) {
                event.preventDefault();
                root.classList.contains('is-open') ? close() : open();
            });
        });

        closers.forEach(function(button) {
            button.addEventListener('click', function(event) {
                event.preventDefault();
                close();
            });
        });

        forms.forEach(function(form) {
            var input = form.querySelector('[data-search-input]');
            var box = form.querySelector('[data-search-suggestions]');
            var debouncedSuggest = debounce(function() { fetchSuggestions(input, box); }, 180);

            if (input) {
                input.addEventListener('input', debouncedSuggest);
                input.addEventListener('focus', function() {
                    if (input.value.trim().length >= 2) fetchSuggestions(input, box);
                });
            }

            form.addEventListener('submit', function(event) {
                if (!input || input.value.trim().length === 0) {
                    event.preventDefault();
                    if (input) input.focus();
                }
            });
        });

        document.addEventListener('click', function(event) {
            var insideModal = modal && modal.contains(event.target);
            if (!root.contains(event.target) && !insideModal && root.classList.contains('is-open')) {
                close();
            }
        });

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && root.classList.contains('is-open')) {
                close();
            }
        });
    }

    function escapeHtml(value) {
        return String(value).replace(/[&<>"']/g, function(char) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[char];
        });
    }

    function escapeAttr(value) {
        return escapeHtml(value).replace(/`/g, '&#096;');
    }

    function boot() {
        document.querySelectorAll('[data-search-widget]').forEach(initSearchWidget);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
</script>
HTML;
    }
}
