@extends('layouts.app')

@section('title', $page->meta_title ?? $page->title)
@section('description', $page->meta_description ?? the_excerpt($page, 150))

@section('content')
<div class="container section">
    <div style="max-width: 800px; margin: 0 auto;">
        <!-- Page Header -->
        <header class="text-center mb-12">
            <h1 class="mb-4" style="font-size: clamp(2rem, 4vw, 3rem); line-height: 1.2;">{{ $page->title }}</h1>
            @if($page->excerpt)
                <p class="text-muted text-xl" style="line-height: 1.6;">{{ $page->excerpt }}</p>
            @endif
        </header>

        <!-- Cover Image -->
        @if($page->featured_image)
            <div class="mb-12">
                <img src="{{ $page->featured_image }}" alt="{{ $page->title }}" style="width: 100%; height: auto; border-radius: var(--theme-card-radius); box-shadow: var(--shadow-md);">
            </div>
        @endif

        <!-- Content -->
        <article class="article-content">
            {!! render_dynamic_blocks($page->content_html) !!}
        </article>
    </div>
</div>
@endsection
