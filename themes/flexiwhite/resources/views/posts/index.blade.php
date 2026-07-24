@extends('layouts.app')

@section('title', _l('Blog'))
@section('description', _l('Read our latest articles and updates.'))

@section('breadcrumb')
    @include('partials.breadcrumb', ['items' => [
        ['label' => _l('Home'), 'url' => url('/')],
        ['label' => _l('Blog'), 'url' => null],
    ]])
@endsection

@section('content')
@php
    $columns = (int) theme_get_option('flexiwhite_posts_columns', 3);
    $defaultView = theme_get_option('flexiwhite_posts_default_view', 'grid');
    $cardStyle = theme_get_option('flexiwhite_posts_card_style', 'image_first');
    $isTitleFirst = $cardStyle === 'title_first';
@endphp

<div class="container section">
    @if(theme_get_option('flexiwhite_blog_header_show', 'show') === 'show')
    <section class="fw-hero" style="text-align: left;">
        <h1 class="fw-hero-title" style="margin-bottom: 0.25rem;">{{ theme_get_option('flexiwhite_blog_title', _l('Blog')) }}</h1>
        <p class="fw-hero-subtitle" style="margin-left: 0; margin-right: 0; max-width: none;">{{ theme_get_option('flexiwhite_blog_subtitle', _l('Insights, updates, and tutorials from our team.')) }}</p>
    </section>
    @endif

    <div class="listing-header" style="justify-content: flex-end;">
        @include('partials.listing-toolbar', [
            'defaultView' => $defaultView,
            'target' => 'posts-listing',
        ])
    </div>

    <div id="posts-listing"
         class="listing-container"
         data-columns="{{ $columns }}"
         style="--listing-columns: {{ $columns }}">
        <script>/*<![CDATA[*/(function(){try{var v=localStorage.getItem('polycms_view_posts-listing');if(v==='list'||v==='grid')document.currentScript.parentElement.classList.add('is-'+v);else document.currentScript.parentElement.classList.add('is-grid')}catch(e){document.currentScript.parentElement.classList.add('is-grid')}})();/*]]>*/</script>

        @forelse($posts ?? [] as $post)
            <article class="listing-card {{ $isTitleFirst ? 'card-title-first' : '' }}">
                {{-- Image --}}
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

                {{-- Content --}}
                <div class="listing-card__body">
                    <div class="listing-card__meta">
                        @if($post->categories->count() > 0)
                            @php $displayCategory = $post->categories->sortByDesc('depth')->first(); @endphp
                            <a href="{{ $displayCategory->frontend_url ?? '#' }}" class="badge">{{ $displayCategory->name }}</a>
                        @endif
                    </div>

                    <h3 class="listing-card__title">
                        <a href="{{ $post->frontend_url }}">{{ $post->title }}</a>
                    </h3>

                    @if($post->excerpt || $post->content_html)
                        <p class="listing-card__excerpt">
                            {{ Str::limit(strip_tags($post->excerpt ?: $post->content_html), 120) }}
                        </p>
                    @endif

                    <div class="listing-card__author">
                        @if($post->user)
                            <span>{{ _l('By') }} <a href="{{ route('authors.show', $post->user) }}">{{ $post->user->name }}</a></span>
                        @endif
                        
                        @if(theme_get_option('flexiwhite_post_show_date', 'show') === 'show')
                            @if($post->user) <span style="margin: 0 0.5rem; color: var(--geist-accents-3);">&bull;</span> @endif
                            <span>{{ format_post_date($post->published_at ?? $post->created_at) }}</span>
                        @endif
                    </div>
                </div>
            </article>
        @empty
            <div class="listing-empty">
                <p>{{ _l('No posts found.') }}</p>
            </div>
        @endforelse
    </div>

    @if(isset($posts) && method_exists($posts, 'links'))
        <div style="margin-top: 4rem;">
            {{ $posts->links() }}
        </div>
    @endif
</div>
@endsection
