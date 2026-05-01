@php
    $heading = $attrs['heading'] ?? __('Latest Updates');
    $count = (int) ($attrs['count'] ?? 6);
    $columns = (int) ($attrs['columns'] ?? 3);
    $showViewAll = $attrs['show_view_all'] ?? true;
    $viewAllUrl = $attrs['view_all_url'] ?? theme_permalink_url('posts', '', 'archive');

    $posts = \App\Models\Post::where('type', 'post')
        ->where('status', 'published')
        ->latest('published_at')
        ->take($count)
        ->get();
@endphp

@if($posts->count() > 0)
<section class="section">
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h2 style="margin: 0;">{{ $heading }}</h2>
            @if($showViewAll)
                <a href="{{ $viewAllUrl }}" class="btn btn-secondary">{{ __('View All') }} &rarr;</a>
            @endif
        </div>

        <div class="listing-container is-grid" style="--listing-columns: {{ $columns }}">
            @foreach($posts as $post)
                <article class="listing-card">
                    {{-- Image --}}
                    <a href="{{ $post->frontend_url }}" class="listing-card__image">
                        @php $thumbnail = $post->featured_image ?: get_default_post_image($post); @endphp
                        @if($thumbnail)
                            <img src="{{ $thumbnail }}" alt="{{ $post->title }}" loading="lazy">
                        @else
                            <div class="listing-card__no-image">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/>
                                    <polyline points="21 15 16 10 5 21"/>
                                </svg>
                            </div>
                        @endif
                    </a>

                    {{-- Content --}}
                    <div class="listing-card__body">
                        <div class="listing-card__meta">
                            @if($post->categories->count() > 0)
                                @php $displayCategory = $post->categories->whereNotNull('parent_id')->first() ?? $post->categories->first(); @endphp
                                <a href="{{ $displayCategory->frontend_url ?? '#' }}" class="badge">{{ $displayCategory->name }}</a>
                            @endif
                            <span class="listing-card__date">{{ format_post_date($post->published_at) }}</span>
                        </div>

                        <h3 class="listing-card__title">
                            <a href="{{ $post->frontend_url }}">{{ $post->title }}</a>
                        </h3>

                        @if($post->excerpt || $post->content_html)
                            <p class="listing-card__excerpt">
                                {{ Str::limit(strip_tags($post->excerpt ?: $post->content_html), 120) }}
                            </p>
                        @endif

                        @if($post->user)
                            <div class="listing-card__author">
                                <span>{{ _l('By') }} {{ $post->user->name }}</span>
                            </div>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>

@endif
