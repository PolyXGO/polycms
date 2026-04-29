@extends('layouts.app')

@section('title', $category->name ?? 'Category')

@section('description', $category->description ?? '')

@section('content')
<section class="page-header-section">
    <div class="container">
        <div class="page-header-content">
            @if(!empty($category->image))
                <img src="{{ $category->image }}" alt="{{ $category->name }}" class="category-banner-image">
            @endif
            <h1 class="page-header-title">{{ $category->name }}</h1>
            @if(!empty($category->description))
                <p class="page-header-excerpt">{{ $category->description }}</p>
            @endif
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
                            <span>Read More</span>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </article>
            @empty
                <div class="empty-state-container">
                    <div class="empty-state-icon">
                        <i class="far fa-folder-open"></i>
                    </div>
                    <p class="empty-state-text">No posts found in this category.</p>
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
