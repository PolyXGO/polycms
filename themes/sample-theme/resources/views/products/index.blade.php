@extends('layouts.app')

@section('title', _l('Products'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        {{-- Page Header --}}
        <header class="mb-8">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-2">{{ _l('Products') }}</h1>
            <p class="text-gray-600 dark:text-gray-400">{{ _l('Browse our product catalog') }}</p>
        </header>
        
        {{-- Products Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($products ?? [] as $product)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md hover:shadow-lg transition-shadow overflow-hidden">
                    {{-- Product Image --}}
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
                        {{-- Product Title --}}
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                            <a href="{{ url('/products/' . $product->slug) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                {{ $product->name }}
                            </a>
                        </h2>
                        
                        {{-- Product Price --}}
                        <div class="flex items-center gap-2 mb-3">
                            @if($product->sale_price && $product->sale_price < $product->price)
                                <span class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">
                                    ${{ number_format($product->sale_price, 2) }}
                                </span>
                                <span class="text-lg text-gray-400 line-through">
                                    ${{ number_format($product->price, 2) }}
                                </span>
                            @else
                                <span class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">
                                    ${{ number_format($product->price, 2) }}
                                </span>
                            @endif
                        </div>
                        
                        {{-- Product Excerpt --}}
                        @if(!empty($product->short_description))
                            <p class="text-gray-600 dark:text-gray-400 mb-4 line-clamp-2">
                                {{ $product->short_description }}
                            </p>
                        @endif
                        
                        {{-- View Product --}}
                        <a href="{{ url('/products/' . $product->slug) }}" class="inline-block w-full text-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                            {{ _l('View Product') }}
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12">
                    <p class="text-gray-500 dark:text-gray-400">{{ _l('No products found.') }}</p>
                </div>
            @endforelse
        </div>
        
        {{-- Pagination --}}
        @if(isset($products) && method_exists($products, 'links'))
            <div class="mt-8">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
