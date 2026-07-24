<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Post;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MenuItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'menu_id',
        'parent_id',
        'title',
        'url',
        'type',
        'linkable_id',
        'linkable_type',
        'target',
        'icon',
        'css_class',
        'order',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
            'active' => 'boolean',
        ];
    }

    /**
     * Get the menu this item belongs to
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    /**
     * Get the parent menu item
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    /**
     * Get child menu items
     */
    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('order');
    }

    /**
     * Get the linked entity (polymorphic)
     */
    public function linkable(): MorphTo
    {
        return $this->morphTo();
    }

    protected ?\Illuminate\Database\Eloquent\Model $resolvedLinkable = null;
    protected bool $resolvedLinkableLoaded = false;

    /**
     * Get the dynamically resolved linkable entity for the current locale (with caching)
     */
    public function getResolvedLinkable(): ?\Illuminate\Database\Eloquent\Model
    {
        if ($this->resolvedLinkableLoaded) {
            return $this->resolvedLinkable;
        }

        $entity = null;
        if ($this->linkable_type && $this->linkable_id) {
            $entity = $this->linkable;
            if (!$entity) {
                $linkableType = $this->linkable_type;
                $linkableId = $this->linkable_id;
                if ($linkableType && $linkableId && class_exists($linkableType)) {
                    $entity = $linkableType::withoutGlobalScope('locale')->find($linkableId);
                }
            }

            if ($entity) {
                $currentLanguage = \App\Helpers\LanguageHelper::getCurrentLanguage() ?: \Illuminate\Support\Facades\App::getLocale();
                if ($currentLanguage && method_exists($entity, 'getTranslation')) {
                    $translation = $entity->getTranslation($currentLanguage);
                    if ($translation) {
                        $entity = $translation;
                    }
                }
            }
        }

        $this->resolvedLinkable = $entity;
        $this->resolvedLinkableLoaded = true;

        return $this->resolvedLinkable;
    }

    /**
     * Get the translated title of the menu item (accessor)
     */
    public function getTitleAttribute($value): ?string
    {
        $title = $value;

        if ($this->linkable_type && $this->linkable_id) {
            $entity = $this->getResolvedLinkable();
            if ($entity) {
                $entityTitle = $entity->title ?? $entity->name ?? null;
                if ($entityTitle) {
                    $title = $entityTitle;
                }
            }
        }

        if ($title) {
            $title = _l($title);
        }

        return \App\Facades\Hook::applyFilters('menu_item.title', $title, $this);
    }

    /**
     * Get the effective URL (dynamically resolved for linked entities, or custom URL)
     *
     * For linkable items (post, page, product, category), the URL is always resolved
     * dynamically from the linked entity so that permalink changes are reflected immediately.
     * The stored 'url' field is only used for 'custom' type items.
     */
    public function getEffectiveUrlAttribute(): ?string
    {
        if ($this->type === 'language' || $this->type === 'search') {
            $url = '#';
        } else {
            // For linkable items, always resolve dynamically
            if ($this->linkable_type) {
                if ($this->linkable_id) {
                    $entity = $this->getResolvedLinkable();
                    if ($entity) {
                        $dynamicUrl = match ($this->linkable_type) {
                            Post::class => $entity->frontend_url,
                            Product::class => $entity->frontend_url ?? null,
                            Category::class => $entity->frontend_url ?? null,
                            default => null,
                        };

                        if ($dynamicUrl) {
                            $url = $dynamicUrl;
                        } else {
                            $url = $this->url ?: null;
                        }
                    } else {
                        $url = $this->url ?: null;
                    }
                } else {
                    // Archive logic: type is set (e.g. Post, Product) but no specific ID
                    if ($this->linkable_type === Post::class || $this->type === 'post') {
                        $url = url(trim(theme_permalink_structure()['posts']['archive'] ?? 'posts', '/'));
                    } elseif ($this->linkable_type === Product::class || $this->type === 'product') {
                        $url = url(trim(theme_permalink_structure()['products']['archive'] ?? 'products', '/'));
                    } else {
                        $url = $this->url ?: null;
                    }
                }
            } else {
                // Also handle cases where type is set directly but linkable_type might be empty
                if (!$this->linkable_id) {
                    if ($this->type === 'post') {
                        $url = url(trim(theme_permalink_structure()['posts']['archive'] ?? 'posts', '/'));
                    } elseif ($this->type === 'product') {
                        $url = url(trim(theme_permalink_structure()['products']['archive'] ?? 'products', '/'));
                    } else {
                        $url = $this->url ?: null;
                    }
                } else {
                    $url = $this->url ?: null;
                }
            }
        }

        return \App\Facades\Hook::applyFilters('menu_item.effective_url', $url, $this);
    }

    /**
     * Get children items accessor
     */
    public function getChildrenAttribute()
    {
        if ($this->type === 'language') {
            return $this->getDynamicLanguageChildren();
        }

        if ($this->relationLoaded('children')) {
            return $this->getRelation('children');
        }

        return $this->children()->get();
    }

    /**
     * Generate dynamic children representing active languages
     */
    public function getDynamicLanguageChildren()
    {
        $activeLangs = \App\Helpers\LanguageHelper::getActiveLanguages();
        $currentLang = \App\Helpers\LanguageHelper::getCurrentLanguage();

        $children = collect([]);
        foreach ($activeLangs as $lang) {
            $child = new self([
                'menu_id' => $this->menu_id,
                'title' => $lang->native_name ?: $lang->name,
                'type' => 'custom',
                'url' => request()->fullUrlWithQuery(['lang' => $lang->code]),
                'active' => $lang->code === $currentLang,
            ]);
            $child->id = 0; // dummy ID
            $child->setAttribute('lang_code', $lang->code);
            $children->push($child);
        }

        return $children;
    }

    /**
     * Check if the language switcher should show the label text.
     */
    public function getShowLabelAttribute(): bool
    {
        if ($this->type !== 'language') {
            return true;
        }

        $url = $this->url;
        if (empty($url) || $url === '#') {
            return true;
        }

        try {
            if (str_starts_with($url, '{')) {
                $config = json_decode($url, true);
                return (bool) ($config['show_label'] ?? true);
            }
        } catch (\Exception $e) {
            // Fallback
        }

        return true;
    }

    /**
     * Get the search widget display style (e.g. icon_modal, icon_expand, form).
     */
    public function getSearchStyleAttribute(): string
    {
        if ($this->type !== 'search') {
            return 'icon_modal';
        }

        $url = $this->url;
        if (empty($url) || $url === '#' || $url === 'search') {
            return 'icon_modal';
        }

        try {
            if (str_starts_with($url, '{')) {
                $config = json_decode($url, true);
                return $config['search_style'] ?? 'icon_modal';
            }
        } catch (\Exception $e) {
            // Fallback
        }

        return 'icon_modal';
    }

    /**
     * Get the search widget input placeholder.
     */
    public function getSearchPlaceholderAttribute(): string
    {
        if ($this->type !== 'search') {
            return _l('Search...');
        }

        $url = $this->url;
        if (empty($url) || $url === '#' || $url === 'search') {
            return _l('Search...');
        }

        try {
            if (str_starts_with($url, '{')) {
                $config = json_decode($url, true);
                return $config['search_placeholder'] ?? _l('Search...');
            }
        } catch (\Exception $e) {
            // Fallback
        }

        return _l('Search...');
    }

    /**
     * Get search custom border color.
     */
    public function getSearchBorderColorAttribute(): string
    {
        if ($this->type !== 'search') return '';
        $url = $this->url;
        if (empty($url) || !str_starts_with($url, '{')) return '';
        try {
            $config = json_decode($url, true);
            return $config['search_border_color'] ?? '';
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Get search custom border width.
     */
    public function getSearchBorderWidthAttribute(): string
    {
        if ($this->type !== 'search') return '';
        $url = $this->url;
        if (empty($url) || !str_starts_with($url, '{')) return '';
        try {
            $config = json_decode($url, true);
            return $config['search_border_width'] ?? '';
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Get search custom border radius.
     */
    public function getSearchBorderRadiusAttribute(): string
    {
        if ($this->type !== 'search') return '';
        $url = $this->url;
        if (empty($url) || !str_starts_with($url, '{')) return '';
        try {
            $config = json_decode($url, true);
            return $config['search_border_radius'] ?? '';
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Get search custom background color.
     */
    public function getSearchBgColorAttribute(): string
    {
        if ($this->type !== 'search') return '';
        $url = $this->url;
        if (empty($url) || !str_starts_with($url, '{')) return '';
        try {
            $config = json_decode($url, true);
            return $config['search_bg_color'] ?? '';
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Get search custom background hover color.
     */
    public function getSearchBgHoverColorAttribute(): string
    {
        if ($this->type !== 'search') return '';
        $url = $this->url;
        if (empty($url) || !str_starts_with($url, '{')) return '';
        try {
            $config = json_decode($url, true);
            return $config['search_bg_hover_color'] ?? '';
        } catch (\Exception $e) {
            return '';
        }
    }
}
