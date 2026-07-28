@extends('layouts.app')

@section('title', 'Posts')

@section('content')
<div class="container mx-auto px-4 py-8">
    <header class="mb-8">
        <h1 class="text-4xl font-bold text-gray-900 dark:text-white">{{ _l('Blog Posts') }}</h1>
    </header>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($posts as $post)
            <article class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                @if($post->featured_image)
                    <img src="{{ $post->featured_image }}" alt="{{ $post->title }}" class="w-full h-48 object-cover">
                @endif
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                        <a href="{{ url('/posts/' . $post->slug) }}" class="hover:text-blue-600 dark:hover:text-blue-400">
                            {{ $post->title }}
                        </a>
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400 text-sm mb-4">
                        {{ the_excerpt($post, 100) }}
                    </p>
                    <div class="flex items-center text-xs text-gray-500 dark:text-gray-500">
                        <span>{{ format_post_date($post->published_at) }}</span>
                    </div>
                </div>
            </article>
        @empty
            <div class="col-span-full text-center py-12">
                <p class="text-gray-600 dark:text-gray-400">{{ _l('No posts found.') }}</p>
            </div>
        @endforelse
    </div>

    @if(isset($posts) && method_exists($posts, 'links'))
        <div class="mt-8">
            {{ $posts->links() }}
        </div>
    @endif
</div>
@endsection
