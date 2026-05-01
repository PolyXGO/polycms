@extends('layouts.app')

@section('title', '#' . $tag->name)
@section('description', $tag->description ?? _l('Posts tagged with') . ' ' . $tag->name)

@section('breadcrumb')
    @include('partials.breadcrumb', ['items' => [
        ['label' => _l('Home'), 'url' => url('/')],
        ['label' => _l('Blog'), 'url' => route('posts.index')],
        ['label' => '#' . $tag->name, 'url' => null],
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
    <div class="listing-header">
        <div>
            <span class="badge" style="margin-bottom: 0.5rem;">{{ _l('Tag') }}</span>
            <h1 class="listing-title">#{{ $tag->name }}</h1>
            @if($tag->description)
                <p class="listing-subtitle">{{ $tag->description }}</p>
            @endif
        </div>
        @include('partials.listing-toolbar', [
            'defaultView' => $defaultView,
            'target' => 'tag-listing',
        ])
    </div>

    <div id="tag-listing"
         class="listing-container {{ $defaultView === 'list' ? 'is-list' : 'is-grid' }}"
         data-columns="{{ $columns }}"
         style="--listing-columns: {{ $columns }}">

        @forelse($posts as $post)
            <article class="listing-card {{ $isTitleFirst ? 'card-title-first' : '' }}">
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
                        <span class="listing-card__date">{{ format_post_date($post->published_at) }}</span>
                    </div>

                    <h3 class="listing-card__title">
                        <a href="{{ $post->frontend_url }}">{{ $post->title }}</a>
                    </h3>

                    @if($post->excerpt)
                        <p class="listing-card__excerpt">{{ Str::limit($post->excerpt, 120) }}</p>
                    @endif

                    @if($post->user)
                        <div class="listing-card__author">
                            <span>{{ _l('By') }} {{ $post->user->name }}</span>
                        </div>
                    @endif
                </div>
            </article>
        @empty
            <div class="listing-empty text-center py-12">
                <p class="text-muted text-lg">{{ _l('No posts found with this tag.') }}</p>
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
