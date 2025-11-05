@extends('layouts.app')

@section('title', 'Blog Posts')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        {{-- Page Header --}}
        <header class="mb-8">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-2">Blog Posts</h1>
            <p class="text-gray-600 dark:text-gray-400">Latest articles and updates</p>
        </header>
        
        {{-- Posts Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse($posts ?? [] as $post)
                <article class="bg-white dark:bg-gray-800 rounded-lg shadow-md hover:shadow-lg transition-shadow overflow-hidden">
                    {{-- Featured Image --}}
                    @if(!empty($post->featured_image))
                        <a href="{{ url('/posts/' . $post->slug) }}">
                            <img src="{{ $post->featured_image }}" alt="{{ $post->title }}" class="w-full h-48 object-cover">
                        </a>
                    @endif
                    
                    <div class="p-6">
                        {{-- Post Meta --}}
                        <div class="flex items-center text-sm text-gray-500 dark:text-gray-400 mb-2">
                            <time datetime="{{ $post->published_at ?? $post->created_at }}">
                                {{ format_post_date($post->published_at ?? $post->created_at) }}
                            </time>
                            @if($post->categories && $post->categories->count() > 0)
                                <span class="mx-2">•</span>
                                <a href="{{ url('/categories/' . $post->categories->first()->slug) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">
                                    {{ $post->categories->first()->name }}
                                </a>
                            @endif
                        </div>
                        
                        {{-- Post Title --}}
                        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-3">
                            <a href="{{ url('/posts/' . $post->slug) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                {{ $post->title }}
                            </a>
                        </h2>
                        
                        {{-- Post Excerpt --}}
                        <p class="text-gray-600 dark:text-gray-400 mb-4 line-clamp-3">
                            {{ the_excerpt($post) }}
                        </p>
                        
                        {{-- Read More --}}
                        <a href="{{ url('/posts/' . $post->slug) }}" class="inline-flex items-center text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-medium">
                            Read More
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                </article>
            @empty
                <div class="col-span-full text-center py-12">
                    <p class="text-gray-500 dark:text-gray-400">No posts found.</p>
                </div>
            @endforelse
        </div>
        
        {{-- Pagination --}}
        @if(isset($posts) && method_exists($posts, 'links'))
            <div class="mt-8">
                {{ $posts->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
