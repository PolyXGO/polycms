@extends('layouts.app')

@section('title', $page->meta_title ?? $page->title)
@section('description', $page->meta_description ?? the_excerpt($page, 150))

@section('content')
@php
    $isHomepage = !empty($is_homepage);
@endphp
<article class="container section {{ $isHomepage ? 'page-full-width' : '' }}">
    @if(!$isHomepage)
    <div class="page-content-wrapper">
        @if(($page->show_title ?? true) !== false)
        <header class="mb-8">
            <h1 class="mb-4">{{ $page->title }}</h1>
        </header>
        @endif
    @endif

        @if(!$isHomepage && $page->featured_image)
            <div class="mb-8">
                <img src="{{ $page->featured_image }}" alt="{{ $page->title }}" style="width: 100%; height: auto; border-radius: var(--theme-card-radius);">
            </div>
        @endif

        <div class="article-content prose">
            {!! render_dynamic_blocks($renderedContent ?? ($page->content_html ?? '')) !!}
        </div>

    @if(!$isHomepage)
    </div>
    @endif
</article>
@endsection
