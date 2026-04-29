@extends('layouts.app')

@section('title', $page->meta_title ?? $page->title)

@section('description', $page->meta_description ?? $page->excerpt)

@section('content')
<article class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <header class="mb-8">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">{{ $page->title }}</h1>
        </header>

        @if($page->featured_image)
            <div class="mb-8">
                <img src="{{ $page->featured_image }}" alt="{{ $page->title }}" class="w-full h-auto rounded-lg">
            </div>
        @endif

        <div class="prose dark:prose-invert max-w-none">
            {!! render_dynamic_blocks($page->content_html) !!}
        </div>
    </div>
</article>
@endsection
