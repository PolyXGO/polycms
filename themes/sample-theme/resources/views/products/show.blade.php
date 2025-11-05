@extends('layouts.app')

@section('title', $product->name ?? 'Product')

@section('description', $product->meta_description ?? $product->short_description ?? '')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            {{-- Product Images --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-4">
                @if($product->media && $product->media->count() > 0)
                    <img src="{{ $product->media->first()->url ?? '' }}" alt="{{ $product->name }}" class="w-full rounded-lg">
                @else
                    <div class="w-full h-96 bg-gray-200 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                        <span class="text-gray-400 dark:text-gray-500">No Image</span>
                    </div>
                @endif
            </div>
            
            {{-- Product Info --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8">
                <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    {{ $product->name }}
                </h1>
                
                {{-- Product Price --}}
                <div class="flex items-center gap-2 mb-6">
                    @if($product->sale_price && $product->sale_price < $product->price)
                        <span class="text-4xl font-bold text-indigo-600 dark:text-indigo-400">
                            ${{ number_format($product->sale_price, 2) }}
                        </span>
                        <span class="text-2xl text-gray-400 line-through">
                            ${{ number_format($product->price, 2) }}
                        </span>
                        <span class="px-3 py-1 bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 rounded-full text-sm font-semibold">
                            Sale
                        </span>
                    @else
                        <span class="text-4xl font-bold text-indigo-600 dark:text-indigo-400">
                            ${{ number_format($product->price, 2) }}
                        </span>
                    @endif
                </div>
                
                {{-- Stock Status --}}
                @if($product->stock_status === 'in_stock')
                    <p class="text-green-600 dark:text-green-400 font-medium mb-4">In Stock</p>
                @elseif($product->stock_status === 'out_of_stock')
                    <p class="text-red-600 dark:text-red-400 font-medium mb-4">Out of Stock</p>
                @else
                    <p class="text-yellow-600 dark:text-yellow-400 font-medium mb-4">On Backorder</p>
                @endif
                
                {{-- Short Description --}}
                @if(!empty($product->short_description))
                    <div class="mb-6">
                        <p class="text-gray-600 dark:text-gray-400">
                            {{ $product->short_description }}
                        </p>
                    </div>
                @endif
                
                {{-- Add to Cart Button (placeholder) --}}
                <button class="w-full px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-semibold text-lg mb-6">
                    Add to Cart
                </button>
                
                {{-- Product Meta --}}
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6 space-y-2 text-sm text-gray-600 dark:text-gray-400">
                    @if(!empty($product->sku))
                        <p><strong>SKU:</strong> {{ $product->sku }}</p>
                    @endif
                    @if($product->categories && $product->categories->count() > 0)
                        <p>
                            <strong>Categories:</strong>
                            @foreach($product->categories as $category)
                                <a href="{{ url('/categories/' . $category->slug) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">
                                    {{ $category->name }}
                                </a>
                                @if(!$loop->last), @endif
                            @endforeach
                        </p>
                    @endif
                </div>
            </div>
        </div>
        
        {{-- Product Description --}}
        @if(!empty($product->description_html))
            <div class="mt-8 bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Description</h2>
                <div class="prose prose-lg dark:prose-invert max-w-none">
                    {!! $product->description_html !!}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
