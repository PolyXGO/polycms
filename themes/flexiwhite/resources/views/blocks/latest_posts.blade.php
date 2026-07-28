@php
    $heading = $attrs['heading'] ?? __('Latest Updates');
    $count = (int) ($attrs['count'] ?? 6);
    $columns = (int) ($attrs['columns'] ?? 3);
    $showViewAll = $attrs['show_view_all'] ?? true;
    $viewAllUrl = $attrs['view_all_url'] ?? theme_permalink_url('posts', '', 'archive');
    
    $categoryId = $attrs['category_id'] ?? '';
    $offset = (int) ($attrs['offset'] ?? 0);
    $showMedia = $attrs['show_media'] ?? true;
    $showTitle = $attrs['show_title'] ?? true;
    $showCategories = $attrs['show_categories'] ?? true;
    $showExcerpt = $attrs['show_excerpt'] ?? true;
    $showAuthor = $attrs['show_author'] ?? true;
    $showDate = $attrs['show_date'] ?? true;

    $query = \App\Models\Post::where('type', 'post')
        ->where('status', 'published')
        ->where('locale', app()->getLocale())
        ->latest('published_at');

    if (!empty($categoryId)) {
        // Resolve localized category if needed to match localized posts
        if (class_exists(\App\Models\Category::class) && in_array(\App\Traits\HasTranslations::class, class_uses_recursive(\App\Models\Category::class))) {
            $category = \App\Models\Category::withoutGlobalScope('locale')->find($categoryId);
            if ($category && isset($category->locale)) {
                $currentLocale = app()->getLocale();
                if ($category->locale !== $currentLocale) {
                    $translatedCategory = $category->getTranslation($currentLocale);
                    if ($translatedCategory) {
                        $categoryId = $translatedCategory->id;
                    }
                }
            }
        }
        $query->whereHas('categories', function($q) use ($categoryId) {
            $q->where('categories.id', $categoryId);
        });
    } else {
        // "All categories" - but only categories of the current locale to prevent mixup
        $query->whereHas('categories', function($q) {
            $q->where('categories.locale', app()->getLocale());
        });
    }

    if ($offset > 0) {
        $query->skip($offset);
    }

    $posts = $query->take($count)->get();

    // Support layout and spacing settings
    $margin = $attrs['margin'] ?? '';
    $padding = $attrs['padding_css'] ?? $attrs['padding'] ?? '';

    $inlineStyles = [];
    if ($margin !== '') {
        $inlineStyles[] = "margin: {$margin}";
    }
    if ($padding !== '') {
        $inlineStyles[] = "padding: {$padding}";
    }
    $styleAttr = !empty($inlineStyles) ? 'style="' . implode('; ', $inlineStyles) . '"' : '';
@endphp

@if($posts->count() > 0)
<section class="section" {!! $styleAttr !!}>
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h2 style="margin: 0;">{{ $heading }}</h2>
            @if($showViewAll)
                <a href="{{ $viewAllUrl }}" target="{{ get_link_target($viewAllUrl) }}" class="btn btn-secondary">{{ __('View All') }} &rarr;</a>
            @endif
        </div>

        <div class="listing-container is-grid" style="--listing-columns: {{ $columns }}">
            @foreach($posts as $post)
                <article class="listing-card">
                    {{-- Image --}}
                    @if($showMedia)
                    <a href="{{ $post->frontend_url }}" class="listing-card__image">
                        @php $thumbnail = $post->featured_image_url; @endphp
                        @if($thumbnail)
                            <img src="{{ $thumbnail }}" alt="{{ $post->title }}" {!! media_lazy_attr() !!}>
                        @else
                            <div class="listing-card__no-image">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/>
                                    <polyline points="21 15 16 10 5 21"/>
                                </svg>
                            </div>
                        @endif
                    </a>
                    @endif

                    {{-- Content --}}
                    @if($showTitle || $showCategories || $showExcerpt || $showAuthor || $showDate)
                    <div class="listing-card__body">
                        @if($showCategories)
                        <div class="listing-card__meta">
                            @if($post->categories->count() > 0)
                                @php $displayCategory = $post->categories->whereNotNull('parent_id')->first() ?? $post->categories->first(); @endphp
                                <a href="{{ $displayCategory->frontend_url ?? '#' }}" class="badge">{{ $displayCategory->name }}</a>
                            @endif
                        </div>
                        @endif

                        @if($showTitle)
                        <h3 class="listing-card__title">
                            <a href="{{ $post->frontend_url }}">{{ $post->title }}</a>
                        </h3>
                        @endif

                        @if($showExcerpt && ($post->excerpt || $post->content_html))
                            <p class="listing-card__excerpt">
                                {{ Str::limit(strip_tags($post->excerpt ?: $post->content_html), 120) }}
                            </p>
                        @endif

                        @if($showAuthor || $showDate)
                        <div class="listing-card__author">
                            @if($showAuthor && $post->user)
                                <span>{{ _l('By') }} <a href="{{ route('authors.show', $post->user) }}">{{ $post->user->name }}</a></span>
                            @endif
                            
                            @if($showDate && theme_get_option('flexiwhite_post_show_date', 'show') === 'show')
                                @if($showAuthor && $post->user) <span style="margin: 0 0.5rem; color: var(--geist-accents-3);">&bull;</span> @endif
                                <span>{{ format_post_date($post->published_at ?? $post->created_at) }}</span>
                            @endif
                        </div>
                        @endif
                    </div>
                    @endif
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif
