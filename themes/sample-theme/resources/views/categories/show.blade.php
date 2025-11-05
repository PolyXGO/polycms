@extends('layouts.app')

@section('title', ($category->name ?? 'Category') . ' - ' . ($site_title ?? config('app.name')))

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        {{-- Category Header --}}
        <header class="mb-8">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-2">
                {{ $category->name ?? _l('Category') }}
            </h1>
            @if(!empty($category->description))
                <p class="text-gray-600 dark:text-gray-400">
                    {{ $category->description }}
                </p>
            @endif
        </header>
        
        {{-- Category Posts/Products --}}
        @if(isset($posts) && $posts->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($posts as $post)
                    <article class="bg-white dark:bg-gray-800 rounded-lg shadow-md hover:shadow-lg transition-shadow overflow-hidden">
                        @if(!empty($post->featured_image))
                            <a href="{{ url('/posts/' . $post->slug) }}">
                                <img src="{{ $post->featured_image }}" alt="{{ $post->title }}" class="w-full h-48 object-cover">
                            </a>
                        @endif
                        
                        <div class="p-6">
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                                <a href="{{ url('/posts/' . $post->slug) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                    {{ $post->title }}
                                </a>
                            </h2>
                            <p class="text-gray-600 dark:text-gray-400 mb-4 line-clamp-2">
                                {{ the_excerpt($post) }}
                            </p>
                            <a href="{{ url('/posts/' . $post->slug) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-medium">
                                {{ _l('Read More →') }}
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        @elseif(isset($products) && $products->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($products as $product)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md hover:shadow-lg transition-shadow overflow-hidden">
                        <a href="{{ url('/products/' . $product->slug) }}">
                            @if($product->media && $product->media->count() > 0)
                                <img src="{{ $product->media->first()->url ?? '' }}" alt="{{ $product->name }}" class="w-full h-48 object-cover">
                            @else
                                <div class="w-full h-48 bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                    <span class="text-gray-400 dark:text-gray-500">{{ _l('No Image') }}</span>
                                </div>
                            @endif
                        </a>
                        
                        <div class="p-6">
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                                <a href="{{ url('/products/' . $product->slug) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                    {{ $product->name }}
                                </a>
                            </h2>
                            <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400 mb-4">
                                ${{ number_format($product->price, 2) }}
                            </div>
                            <a href="{{ url('/products/' . $product->slug) }}" class="inline-block w-full text-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                                {{ _l('View Product') }}
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-12">
                <p class="text-gray-500 dark:text-gray-400">{{ _l('No items found in this category.') }}</p>
            </div>
        @endif
        
        {{-- Pagination --}}
        @if((isset($posts) || isset($products)) && (method_exists($posts ?? $products, 'links')))
            <div class="mt-8">
                {{ ($posts ?? $products)->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
