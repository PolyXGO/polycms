@extends('layouts.app')

@section('title', __('Blog Posts'))

@section('content')
<section class="page-header-section">
    <div class="container">
        <div class="page-header-content">
            <h1 class="page-header-title">{{ __('Blog Posts') }}</h1>
            <p class="page-header-excerpt">{{ __('Latest articles and updates') }}</p>
        </div>
    </div>
</section>

<section class="page-content-section">
    <div class="container">
        <div class="grid" style="gap: 25px;">
            @forelse($posts ?? [] as $post)
                <article class="post-card">
                    @if(!empty($post->featured_image))
                        <a href="{{ $post->frontend_url }}">
                            <img src="{{ $post->featured_image }}" alt="{{ $post->title }}">
                        </a>
                    @endif

                    <div class="post-card-content">
                        <div class="post-meta">
                            <time datetime="{{ $post->published_at ?? $post->created_at }}">
                                <i class="far fa-calendar" style="margin-right: 6px;"></i>
                                {{ format_post_date($post->published_at ?? $post->created_at) }}
                            </time>
                            @if($post->categories && $post->categories->count() > 0)
                                <span>•</span>
                                <a href="{{ route('categories.show', ['slug' => $post->categories->first()->slug]) }}" class="category-link">
                                    <i class="far fa-folder" style="margin-right: 4px;"></i>
                                    {{ $post->categories->first()->name }}
                                </a>
                            @endif
                        </div>

                        <h2 class="post-title">
                            <a href="{{ $post->frontend_url }}">
                                {{ $post->title }}
                            </a>
                        </h2>

                        <p class="post-excerpt line-clamp-3">
                            {{ the_excerpt($post) }}
                        </p>

                        <a href="{{ $post->frontend_url }}" class="read-more">
                            <span>{{ __('Read More') }}</span>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </article>
            @empty
                <div class="empty-state-container">
                    <div class="empty-state-icon">
                        <i class="far fa-file-alt"></i>
                    </div>
                    <p class="empty-state-text">{{ __('No posts found.') }}</p>
                </div>
            @endforelse
        </div>

        @if(isset($posts) && method_exists($posts, 'links'))
            <div class="pagination" style="margin-top: 50px;">
                {{ $posts->links() }}
            </div>
        @endif
    </div>
</section>
@endsection
