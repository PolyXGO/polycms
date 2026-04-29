@extends('layouts.app')

@section('title', _l('Home'))

@section('content')
    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1 class="hero-title">{{ $site_title ?? config('app.name', 'PolyCMS') }}</h1>
            <p class="hero-subtitle">
                {{ $tagline ?? _l('A modern, lightweight, and incredibly fast content management system designed for maximum flexibility and performance.') }}
            </p>
            <div style="display: flex; justify-content: center; gap: 1rem; margin-top: 2rem;">
                <a href="{{ url('/posts') }}" class="btn btn-primary btn-lg">{{ _l('Read the Blog') }}</a>
                <a href="#" class="btn btn-secondary btn-lg">{{ _l('Learn More') }}</a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="section" style="background-color: var(--geist-accents-1);">
        <div class="container">
            <div class="text-center mb-8">
                <h2>{{ _l('Why Choose Us') }}</h2>
                <p class="text-muted">{{ _l('Built for speed, designed for scale.') }}</p>
            </div>

            <div class="grid-list">
                <!-- Feature 1 -->
                <div class="card" style="padding: 2rem; background-color: var(--geist-background); border: 1px solid var(--geist-accents-2); border-radius: var(--radius);">
                    <h3 class="card-title">{{ _l('Blazing Fast') }}</h3>
                    <p class="text-muted mb-0">
                        {{ _l('Optimized architecture ensures your pages load in milliseconds, delivering the best experience to your users.') }}
                    </p>
                </div>
                <!-- Feature 2 -->
                <div class="card" style="padding: 2rem; background-color: var(--geist-background); border: 1px solid var(--geist-accents-2); border-radius: var(--radius);">
                    <h3 class="card-title">{{ _l('Secure by Default') }}</h3>
                    <p class="text-muted mb-0">
                        {{ _l('Enterprise-grade security built into the core. Your data is encrypted, protected, and always safe.') }}
                    </p>
                </div>
                <!-- Feature 3 -->
                <div class="card" style="padding: 2rem; background-color: var(--geist-background); border: 1px solid var(--geist-accents-2); border-radius: var(--radius);">
                    <h3 class="card-title">{{ _l('Modern Design') }}</h3>
                    <p class="text-muted mb-0">
                        {{ _l('Beautiful, responsive layouts right out of the box. Easily customize everything to match your brand.') }}
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Latest Posts Section -->
    @if(isset($latest_posts) && $latest_posts->count() > 0)
    <section class="section">
        <div class="container">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h2 style="margin: 0;">{{ _l('Latest Updates') }}</h2>
                <a href="{{ url('/posts') }}" class="btn btn-secondary">{{ _l('View All') }} &rarr;</a>
            </div>

            <div class="grid-list">
                @foreach($latest_posts as $post)
                    <article class="card">
                        <a href="{{ $post->frontend_url }}" class="card-image-wrapper">
                            @if($post->featured_image)
                                <img src="{{ $post->featured_image }}" alt="{{ $post->title }}" loading="lazy">
                            @else
                                <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: var(--geist-accents-4);">
                                    <span>{{ _l('No Image') }}</span>
                                </div>
                            @endif
                        </a>
                        <div class="card-meta">
                            @if($post->categories->count() > 0)
                                @php $displayCategory = $post->categories->whereNotNull('parent_id')->first() ?? $post->categories->first(); @endphp
                                <span class="badge">{{ $displayCategory->name }}</span>
                            @endif
                            <span>{{ format_post_date($post->published_at) }}</span>
                        </div>
                        <h3 class="card-title">
                            <a href="{{ $post->frontend_url }}">{{ $post->title }}</a>
                        </h3>
                        <p class="card-excerpt">
                            {{ the_excerpt($post, 120) }}
                        </p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
    @endif
@endsection
