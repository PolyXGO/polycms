@extends('layouts.app')

@section('title', 'Products')

@section('content')
<div class="container mx-auto px-4 py-8">
    <header class="mb-8">
        <h1 class="text-4xl font-bold text-gray-900 dark:text-white">{{ _l('Products') }}</h1>
    </header>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($products as $product)
            <article class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                @if($product->primaryImage())
                    <img src="{{ $product->primaryImage()->url }}" alt="{{ $product->name }}" class="w-full h-48 object-cover">
                @endif
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                        <a href="{{ url('/products/' . $product->slug) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">
                            {{ $product->name }}
                        </a>
                    </h2>
                    @if($product->short_description)
                        <p class="text-gray-600 dark:text-gray-400 text-sm mb-4">
                            {{ Str::limit($product->short_description, 100) }}
                        </p>
                    @endif
                    <div class="flex items-center justify-between">
                        <span class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ format_currency($product->effective_price) }}
                        </span>
                        @if($product->isInStock())
                            <span class="px-2 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded text-xs">{{ _l('In Stock') }}</span>
                        @endif
                    </div>
                </div>
            </article>
        @empty
            <div class="col-span-full text-center py-12">
                <p class="text-gray-600 dark:text-gray-400">{{ _l('No products found.') }}</p>
            </div>
        @endforelse
    </div>

    @if(isset($products) && method_exists($products, 'links'))
        <div class="mt-8">
            {{ $products->links() }}
        </div>
    @endif
</div>
@endsection
