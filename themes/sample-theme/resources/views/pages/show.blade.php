@extends('layouts.app')

@section('title', $page->title ?? 'Page')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <article class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-6">
                {{ $page->title ?? 'Page' }}
            </h1>
            
            <div class="prose prose-lg dark:prose-invert max-w-none">
                {!! $page->content_html ?? '' !!}
            </div>
        </article>
    </div>
</div>
@endsection


