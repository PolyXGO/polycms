@extends('layouts.app')

@section('title', $site_title ?? config('app.name', 'PolyCMS'))

@section('content')
    {{-- Hero Section --}}
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">
                    {{ $site_title ?? 'Welcome to PolyCMS' }}
                </h1>
                @if(!empty($tagline))
                    <p class="hero-subtitle">
                        {{ $tagline }}
                    </p>
                @endif
                <p class="hero-tagline">
                    Building the future, one solution at a time
                </p>
                <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                    <a href="{{ url('/#about') }}" class="btn btn-outline btn-lg">Learn More</a>
                    <a href="{{ url('/#contact') }}" class="btn btn-primary btn-lg">Get Started</a>
                </div>
            </div>
        </div>
    </section>

    {{-- About Section --}}
    <section class="section about-section" id="about">
        <div class="container">
            <h2 class="section-title">About Us</h2>
            <p class="section-subtitle">
                We are a dedicated team committed to delivering excellence and innovation in everything we do.
            </p>
            <div class="grid grid-cols-3">
                <div class="feature-box">
                    <div class="feature-icon">🚀</div>
                    <h3 class="feature-title">Innovation</h3>
                    <p class="feature-text">Cutting-edge solutions that drive your business forward</p>
                </div>
                <div class="feature-box">
                    <div class="feature-icon">💎</div>
                    <h3 class="feature-title">Quality</h3>
                    <p class="feature-text">Uncompromising standards in every project we deliver</p>
                </div>
                <div class="feature-box">
                    <div class="feature-icon">🤝</div>
                    <h3 class="feature-title">Partnership</h3>
                    <p class="feature-text">Building lasting relationships with our clients</p>
                </div>
            </div>
        </div>
    </section>

<div class="container" style="padding: 4rem 1rem;">

    {{-- Recent Posts Section --}}
    @if(isset($posts) && $posts->count() > 0)
        <section class="section">
            <h2 class="section-title">Latest News</h2>
            <p class="section-subtitle">Stay updated with our latest articles and insights</p>
            <div class="grid grid-cols-3">
                @foreach($posts->take(6) as $post)
                    <article class="card">
                        @if(!empty($post->featured_image))
                            <img src="{{ $post->featured_image }}" alt="{{ $post->title }}" class="card-image">
                        @endif
                        
                        <div class="card-body">
                            <div style="font-size: 0.875rem; color: var(--color-gray-500); margin-bottom: 0.5rem;">
                                <time datetime="{{ $post->published_at ?? $post->created_at }}">
                                    {{ format_post_date($post->published_at ?? $post->created_at, 'M j, Y') }}
                                </time>
                            </div>
                            
                            <h3 class="card-title">
                                <a href="{{ url('/posts/' . $post->slug) }}">
                                    {{ $post->title }}
                                </a>
                            </h3>
                            
                            <p class="card-text line-clamp-2">
                                {{ the_excerpt($post, 100) }}
                            </p>
                            
                            <a href="{{ url('/posts/' . $post->slug) }}" class="btn btn-primary btn-sm">
                                Read More
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
            
            <div class="text-center mt-xl">
                <a href="{{ url('/posts') }}" class="btn btn-primary btn-lg">
                    View All Posts
                </a>
            </div>
        </section>
    @endif

    {{-- Featured Products Section --}}
    @if(isset($products) && $products->count() > 0)
        <section class="section about-section">
            <h2 class="section-title">Featured Products</h2>
            <p class="section-subtitle">Discover our premium selection of products</p>
            <div class="grid grid-cols-3">
                @foreach($products->take(6) as $product)
                    <div class="card">
                        @if($product->media && $product->media->count() > 0)
                            <img src="{{ $product->media->first()->url ?? '' }}" alt="{{ $product->name }}" class="card-image">
                        @endif
                        
                        <div class="card-body">
                            <h3 class="card-title">
                                <a href="{{ url('/products/' . $product->slug) }}">
                                    {{ $product->name }}
                                </a>
                            </h3>
                            
                            <div style="font-size: 1.5rem; font-weight: bold; color: var(--color-primary); margin-bottom: 1rem;">
                                ${{ number_format($product->price, 2) }}
                            </div>
                            
                            <a href="{{ url('/products/' . $product->slug) }}" class="btn btn-primary" style="width: 100%;">
                                View Product
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <div class="text-center mt-xl">
                <a href="{{ url('/products') }}" class="btn btn-primary btn-lg">
                    View All Products
                </a>
            </div>
        </section>
    @endif

    {{-- Contact Section --}}
    <section class="section" id="contact">
        <div class="container">
            <h2 class="section-title">Get In Touch</h2>
            <p class="section-subtitle">
                Have a question or want to work with us? We'd love to hear from you.
            </p>
            <div class="text-center">
                <a href="mailto:contact@example.com" class="btn btn-primary btn-lg">
                    Contact Us
                </a>
            </div>
        </div>
    </section>
</div>
@endsection
