@extends('layouts.app')

@section('title', $tag->name)

@section('content')
<section class="page-header-section">
    <div class="container">
        <div class="page-header-content">
            <div class="page-header-badge">
                <i class="fas fa-tag"></i>
                <span>TAG</span>
            </div>
            <h1 class="page-header-title">{{ $tag->name }}</h1>
            @if($tag->description)
                <div class="page-header-excerpt">
                    {!! $tag->description !!}
                </div>
            @endif
        </div>
    </div>
</section>

<section class="page-content-section">
    <div class="container">
        @if($contentType === 'post')
            {{-- Posts Grid --}}
            <div class="grid">
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
                                <span>Read More</span>
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </article>
                @empty
                    <div class="empty-state-container">
                        <div class="empty-state-icon">
                            <i class="far fa-file-alt"></i>
                        </div>
                        <p class="empty-state-text">No posts found with this tag.</p>
                    </div>
                @endforelse
            </div>

            @if(isset($posts) && method_exists($posts, 'links'))
                <div class="pagination" style="margin-top: 50px;">
                    {{ $posts->links() }}
                </div>
            @endif
        @else
            {{-- Products Grid --}}
            <div class="grid">
                @forelse($products ?? [] as $product)
                    <div class="product-card">
                        @if(!empty($product->featured_image))
                            <a href="{{ route('products.show', ['slug' => $product->slug]) }}" style="display: block; aspect-ratio: 1; overflow: hidden;">
                                <img src="{{ $product->featured_image }}" alt="{{ $product->name }}" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;">
                            </a>
                        @endif

                        <div style="padding: 20px;">
                            @if($product->categories && $product->categories->count() > 0)
                                <a href="{{ route('product-categories.show', ['slug' => $product->categories->first()->slug]) }}" style="display: inline-block; font-size: 12px; color: #6366f1; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; font-weight: 600;">
                                    {{ $product->categories->first()->name }}
                                </a>
                            @endif

                            <h3 style="margin: 0 0 12px; font-size: 18px; font-weight: 600;">
                                <a href="{{ route('products.show', ['slug' => $product->slug]) }}" style="color: var(--dark); text-decoration: none;">
                                    {{ $product->name }}
                                </a>
                            </h3>

                            @if($product->price)
                                <div style="font-size: 20px; font-weight: 700; color: #6366f1; margin-bottom: 12px;">
                                    ${{ number_format($product->price, 2) }}
                                </div>
                            @endif

                            <a href="{{ route('products.show', ['slug' => $product->slug]) }}" class="primary-btn" style="padding: 10px 20px; font-size: 14px;">
                                <span>View Details</span>
                                <i class="fas fa-arrow-right" style="margin-left: 5px;"></i>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="empty-state-container">
                        <div class="empty-state-icon">
                            <i class="fas fa-box-open"></i>
                        </div>
                        <p class="empty-state-text">No products found with this tag.</p>
                    </div>
                @endforelse
            </div>

            @if(isset($products) && method_exists($products, 'links'))
                <div class="pagination" style="margin-top: 50px;">
                    {{ $products->links() }}
                </div>
            @endif
        @endif
    </div>
</section>
@endsection
