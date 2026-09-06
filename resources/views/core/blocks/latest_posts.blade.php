@php
    $heading = $attrs['heading'] ?? __('Latest Updates');
    $count = (int) ($attrs['count'] ?? 6);
    $columns = (int) ($attrs['columns'] ?? 3);
    $showViewAll = ($attrs['show_view_all'] ?? true) !== false && ($attrs['show_view_all'] ?? '') !== 'no';
    $viewAllUrl = $attrs['view_all_url'] ?? (function_exists('theme_permalink_url') ? theme_permalink_url('posts', '', 'archive') : '/posts');
    
    $categoryId = $attrs['category_id'] ?? '';
    $offset = (int) ($attrs['offset'] ?? 0);
    $showMedia = ($attrs['show_media'] ?? true) !== false;
    $showTitle = ($attrs['show_title'] ?? true) !== false;
    $showCategories = ($attrs['show_categories'] ?? true) !== false;
    $showExcerpt = ($attrs['show_excerpt'] ?? true) !== false;
    $showAuthor = ($attrs['show_author'] ?? true) !== false;
    $showDate = ($attrs['show_date'] ?? true) !== false;

    $selectionMode = $attrs['selection_mode'] ?? 'category';
    $specificPostIds = array_values(array_filter((array)($attrs['specific_post_ids'] ?? $attrs['post_ids'] ?? [])));

    if ($selectionMode === 'specific' && !empty($specificPostIds)) {
        $specificQuery = \App\Models\Post::whereIn('id', $specificPostIds);
        if (!is_admin_user()) {
            $specificQuery->where('status', 'published');
        }
        $fetchedPosts = $specificQuery->get();
        // Sort strictly according to the order in $specificPostIds array (Database-agnostic Collection Sorting)
        $posts = $fetchedPosts->sortBy(function($p) use ($specificPostIds) {
            $pos = array_search($p->id, $specificPostIds);
            return $pos !== false ? $pos : 999999;
        })->values();
    } else {
        $query = \App\Models\Post::where('type', 'post')
            ->where('locale', app()->getLocale())
            ->latest('published_at');

        if (!is_admin_user()) {
            $query->where('status', 'published');
        }

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
        }

        if ($offset > 0) {
            $query->skip($offset);
        }

        $posts = $query->take($count)->get();
    }

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
<section class="poly-latest-posts-section section" {!! $styleAttr !!}>
    <div class="container mx-auto px-4 max-w-7xl">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-2xl lg:text-3xl font-extrabold text-gray-900 dark:text-gray-50 m-0">{{ $heading }}</h2>
            @if($showViewAll)
                <a href="{{ $viewAllUrl }}" class="px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">{{ __('View All') }} &rarr;</a>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-{{ min(max($columns, 1), 4) }} gap-6">
            @foreach($posts as $post)
                <article class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm flex flex-col hover:shadow-md transition-shadow">
                    {{-- Image --}}
                    @if($showMedia)
                    <a href="{{ $post->frontend_url }}" class="aspect-[16/9] w-full block bg-gray-100 dark:bg-gray-800 overflow-hidden">
                        @php $thumbnail = $post->featured_image_url; @endphp
                        @if($thumbnail)
                            <img src="{{ $thumbnail }}" alt="{{ $post->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-gray-400">
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
                    <div class="p-5 flex flex-col flex-1">
                        @if($showCategories || $showDate)
                        <div class="flex items-center gap-2 mb-2.5 text-xs text-gray-500">
                            @if($showCategories && $post->categories->count() > 0)
                                @php $displayCategory = $post->categories->whereNotNull('parent_id')->first() ?? $post->categories->first(); @endphp
                                <span class="bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-semibold px-2 py-0.5 rounded text-[10px] uppercase tracking-wider">
                                    {{ $displayCategory->name }}
                                </span>
                            @endif
                            @if($showDate && $post->published_at)
                                <span>{{ $post->published_at->format('M d, Y') }}</span>
                            @endif
                        </div>
                        @endif

                        @if($showTitle)
                        <h3 class="text-base lg:text-lg font-bold text-gray-900 dark:text-gray-100 mb-2 leading-snug">
                            <a href="{{ $post->frontend_url }}" class="hover:text-admin-theme-primary transition-colors">{{ $post->title }}</a>
                        </h3>
                        @endif

                        @if($showExcerpt && !empty($post->excerpt))
                        <p class="text-xs text-gray-600 dark:text-gray-400 line-clamp-2 leading-relaxed mb-4">
                            {{ $post->excerpt }}
                        </p>
                        @endif

                        @if($showAuthor && $post->user)
                        <div class="text-[11px] text-gray-400 dark:text-gray-500 mt-auto font-medium">
                            By {{ $post->user->name }}
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
