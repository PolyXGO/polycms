@extends('layouts.app')

@section('title', $post->title ?? 'Post')

@section('description', $post->meta_description ?? $post->excerpt ?? '')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <article class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
            {{-- Featured Image --}}
            @if(!empty($post->featured_image))
                <img src="{{ $post->featured_image }}" alt="{{ $post->title }}" class="w-full h-64 md:h-96 object-cover">
            @endif
            
            <div class="p-8">
                {{-- Post Header --}}
                <header class="mb-6">
                    {{-- Post Meta --}}
                    <div class="flex items-center text-sm text-gray-500 dark:text-gray-400 mb-4">
                        <time datetime="{{ $post->published_at ?? $post->created_at }}">
                            {{ format_post_date($post->published_at ?? $post->created_at) }}
                        </time>
                        @if($post->categories && $post->categories->count() > 0)
                            <span class="mx-2">•</span>
                            @foreach($post->categories as $category)
                                <a href="{{ url('/categories/' . $category->slug) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">
                                    {{ $category->name }}
                                </a>
                                @if(!$loop->last), @endif
                            @endforeach
                        @endif
                    </div>
                    
                    {{-- Post Title --}}
                    <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">
                        {{ $post->title }}
                    </h1>
                    
                    {{-- Post Excerpt --}}
                    @if(!empty($post->excerpt))
                        <p class="text-xl text-gray-600 dark:text-gray-400">
                            {{ $post->excerpt }}
                        </p>
                    @endif
                </header>
                
                {{-- Post Content --}}
                <div class="prose prose-lg dark:prose-invert max-w-none mb-8">
                    {!! $post->content_html ?? '' !!}
                </div>
                
                {{-- Post Tags --}}
                @if($post->tags && $post->tags->count() > 0)
                    <div class="flex flex-wrap gap-2 mb-6">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Tags:</span>
                        @foreach($post->tags as $tag)
                            <a href="{{ url('/tags/' . $tag->slug) }}" class="px-3 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-full text-sm hover:bg-indigo-100 dark:hover:bg-indigo-900 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors">
                                {{ $tag->name }}
                            </a>
                        @endforeach
                    </div>
                @endif
                
                {{-- Post Footer --}}
                <footer class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            <span>Published on {{ format_post_date($post->published_at ?? $post->created_at, 'F j, Y') }}</span>
                        </div>
                        {{-- Social Share buttons can go here --}}
                    </div>
                </footer>
            </div>
        </article>
        
        {{-- Sidebar (if active) --}}
        @if(is_active_sidebar('sidebar'))
            <aside class="mt-8">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Sidebar</h3>
                    {{-- Render sidebar widgets here --}}
                    {!! \App\Facades\Widget::renderArea('sidebar') !!}
                </div>
            </aside>
        @endif
    </div>
</div>
@endsection
