@extends('layouts.app')

@section('title', $product->meta_title ?? $product->name)

@section('description', $product->meta_description ?? $product->short_description)

@section('content')
<article class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <header class="mb-8">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">{{ $product->name }}</h1>
            @if($product->short_description)
                <p class="text-xl text-gray-600 dark:text-gray-400">{{ $product->short_description }}</p>
            @endif
        </header>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
            @if($product->primaryImage())
                <div>
                    <img src="{{ $product->primaryImage()->url }}" alt="{{ $product->name }}" class="w-full rounded-lg">
                </div>
            @endif
            <div>
                <div class="mb-4">
                    <span class="text-3xl font-bold text-gray-900 dark:text-white">
                        {{ format_currency($product->effective_price) }}
                    </span>
                    @if($product->isOnSale())
                        <span class="text-lg text-gray-500 line-through ml-2">{{ format_currency($product->price) }}</span>
                    @endif
                </div>
                <div class="mb-4">
                    @if($product->isInStock())
                        <span class="px-3 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-full text-sm">{{ _l('In Stock') }}</span>
                    @else
                        <span class="px-3 py-1 bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 rounded-full text-sm">{{ _l('Out of Stock') }}</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="prose dark:prose-invert max-w-none">
            {!! render_dynamic_blocks($product->description_html) !!}
        </div>

        @if($product->categories->count() > 0)
            <div class="mt-8 pt-8 border-t border-gray-200 dark:border-gray-700">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">{{ _l('Categories:') }}</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach($product->categories as $category)
                        <a href="{{ url('/categories/' . $category->slug) }}" class="px-3 py-1 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-full text-sm hover:bg-gray-200 dark:hover:bg-gray-700">
                            {{ $category->name }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</article>
@endsection
