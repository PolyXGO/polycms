@extends(($post->layout ?? 'default') === 'landing' ? 'layouts.landing' : 'layouts.app')

@section('title', $post->title ?? 'Post')

@section('description', $post->meta_description ?? $post->excerpt ?? '')

@section('content')
@if(($post->layout ?? 'default') === 'landing')
    {!! render_dynamic_blocks($renderedContent ?? ($post->content_html ?? '')) !!}
@else
    @php
        $layout = $post->layout ?? 'default';
        $containerClass = $layout === 'fullwidth' ? 'container-wide' : 'container';
        // Only show gallery if layout is single-column AND media exists (graceful degradation)
        $showGallery = $layout === 'single-column' && !empty($post->media) && count($post->media) > 0;
    @endphp

<section class="page-header-section">
    <div class="{{ $containerClass }}">
        <div class="page-header-content">
            <div class="post-meta" style="justify-content: center; margin-bottom: 20px;">
                <time datetime="{{ $post->published_at ?? $post->created_at }}">
                    <i class="far fa-calendar" style="margin-right: 6px;"></i>
                    {{ format_post_date($post->published_at ?? $post->created_at, 'F j, Y') }}
                </time>
                @if($post->categories && $post->categories->count() > 0)
                    <span>•</span>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                        @foreach($post->categories as $category)
                            <a href="{{ route('categories.show', ['slug' => $category->slug]) }}" class="category-link">
                                <i class="fas fa-folder" style="margin-right: 4px;"></i>
                                {{ $category->name }}
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
            <h1 class="page-header-title">
                {{ $post->title }}
            </h1>
            @if(!empty($post->excerpt))
                <p class="page-header-excerpt">
                    {{ $post->excerpt }}
                </p>
            @endif
        </div>
    </div>
</section>

<section class="page-content-section">
    <div class="{{ $containerClass }}">
        @if($showGallery)
            <div class="product-gallery-top" style="margin-bottom: 50px; max-width: 1000px; margin-left: auto; margin-right: auto;">
                 @include('products.partials.gallery', ['product' => $post])
            </div>
        @endif

        <article class="post-article">
            @if(!empty($post->featured_image) && ($post->show_featured_image ?? true))
                <img src="{{ $post->featured_image }}" alt="{{ $post->title }}" style="width: 100%; height: 450px; object-fit: cover; display: block; border-radius: var(--radius);">
            @endif

            <div style="padding: 3rem 0;">
                <div class="prose" style="margin-bottom: 2.5rem;">
                    {!! render_dynamic_blocks($post->content_html ?? '') !!}
                </div>

                @if($post->tags && $post->tags->count() > 0)
                    <div class="tags-container">
                        <span style="font-weight: 600; font-size: 15px; margin-right: 8px;">
                            <i class="fas fa-tags" style="margin-right: 6px;"></i>{{ __('Tags') }}:
                        </span>
                        @foreach($post->tags as $tag)
                            <a href="{{ route('tags.show', ['slug' => $tag->slug]) }}" class="tag">
                                {{ $tag->name }}
                            </a>
                        @endforeach
                    </div>
                @endif

                <div class="post-footer bordered post-footer-bordered">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                        <div style="display: flex; align-items: center; gap: 12px; color: var(--gray); font-size: 14px;">
                            <i class="far fa-clock"></i>
                            <span>{{ __('Published on') }} {{ format_post_date($post->published_at ?? $post->created_at, 'F j, Y') }}</span>
                        </div>

                    </div>
                </div>
            </div>
        </article>
    </div>
</section>
@endif
@endsection
