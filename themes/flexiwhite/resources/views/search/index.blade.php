@extends('layouts.app')

@section('title', $search ? _l('Search Results for') . ' "' . $search . '"' : _l('Search'))

@push('styles')
    <link rel="stylesheet" href="{{ theme_asset('css/shop.css') }}?v={{ time() }}">
@endpush

@section('breadcrumb')
    @include('partials.breadcrumb', ['items' => [
        ['label' => _l('Home'), 'url' => url('/')],
        ['label' => _l('Search'), 'url' => null],
    ]])
@endsection

@section('content')
<div class="container section">
    {{-- Search Hero Header --}}
    <section class="fw-hero" style="text-align: left;">
        <h1 class="fw-hero-title" style="margin-bottom: 0.25rem;">
            @if(!empty($search))
                {{ _l('Search results for') }} "{{ $search }}"
            @else
                {{ _l('Search') }}
            @endif
        </h1>
        <p class="fw-hero-subtitle" style="margin-left: 0; margin-right: 0; max-width: none;">
            @if($totalResults > 0)
                {{ _l('Found') }} {{ $totalResults }} {{ _l('matching item(s)') }}
            @else
                {{ _l('Explore articles, documentation, products and services.') }}
            @endif
        </p>

        {{-- Search Input Bar --}}
        <form action="{{ url('/search') }}" method="GET" style="margin-top: 1.25rem; max-width: 600px; display: flex; gap: 0.5rem;">
            <input type="hidden" name="target" value="{{ $target }}">
            <input 
                type="text" 
                name="search" 
                value="{{ $search }}" 
                placeholder="{{ _l('Type keywords to search...') }}"
                style="flex: 1; padding: 0.65rem 1rem; border-radius: 8px; border: 1px solid var(--geist-accents-2, #e2e8f0); background: var(--geist-background, #fff); font-size: 0.9rem;"
                required
            >
            <button type="submit" class="btn btn-primary" style="padding: 0.65rem 1.25rem; border-radius: 8px;">
                {{ _l('Search') }}
            </button>
        </form>

        {{-- Target Filter Tabs --}}
        @if(!empty($search))
        <div style="display: flex; gap: 0.5rem; margin-top: 1.5rem; border-bottom: 1px solid var(--geist-accents-2, #e2e8f0); padding-bottom: 0.5rem;">
            <a href="{{ url('/search') }}?search={{ urlencode($search) }}&target=all" 
               class="btn btn-sm {{ $target === 'all' ? 'btn-primary' : 'btn-outline' }}" style="border-radius: 20px;">
                {{ _l('All Results') }} ({{ $totalResults }})
            </a>
            <a href="{{ url('/search') }}?search={{ urlencode($search) }}&target=products" 
               class="btn btn-sm {{ $target === 'products' ? 'btn-primary' : 'btn-outline' }}" style="border-radius: 20px;">
                {{ _l('Products') }} ({{ is_object($products) && method_exists($products, 'total') ? $products->total() : count($products) }})
            </a>
            <a href="{{ url('/search') }}?search={{ urlencode($search) }}&target=posts" 
               class="btn btn-sm {{ $target === 'posts' ? 'btn-primary' : 'btn-outline' }}" style="border-radius: 20px;">
                {{ _l('Articles & Posts') }} ({{ is_object($posts) && method_exists($posts, 'total') ? $posts->total() : count($posts) }})
            </a>
        </div>
        @endif
    </section>

    @if(!empty($search) && $totalResults === 0)
        <div class="empty-state" style="text-align: center; padding: 4rem 1rem;">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin: 0 auto 1rem; color: var(--geist-accents-4);">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 0.5rem;">{{ _l('No results found') }}</h3>
            <p style="color: var(--geist-accents-5); max-width: 450px; margin: 0 auto;">{{ _l('We could not find anything matching your search term. Please try different keywords or refine your query.') }}</p>
        </div>
    @endif

    {{-- Products Section --}}
    @if(count($products) > 0)
        <div style="margin-bottom: 3rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h2 style="font-size: 1.25rem; font-weight: 700; display: flex; align-items: center; gap: 0.5rem;">
                    <span>📦</span> {{ _l('Matching Products') }}
                </h2>
                @if($target === 'all' && is_object($products) && method_exists($products, 'hasMorePages') && $products->hasMorePages())
                    <a href="{{ url('/search') }}?search={{ urlencode($search) }}&target=products" class="btn btn-sm btn-link">
                        {{ _l('View All Products') }} &rarr;
                    </a>
                @endif
            </div>

            <div class="listing-container is-grid" style="--listing-columns: 3">
                @foreach($products as $product)
                    <article class="listing-card">
                        <a href="{{ $product->frontend_url }}" class="listing-card__image">
                            @php $imageUrl = $product->featured_image_url; @endphp
                            @if(!empty($imageUrl))
                                <img src="{{ $imageUrl }}" alt="{{ $product->name }}" {!! media_lazy_attr() !!}>
                            @else
                                <div class="listing-card__no-image">
                                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/>
                                        <polyline points="21 15 16 10 5 21"/>
                                    </svg>
                                </div>
                            @endif
                        </a>
                        <div class="listing-card__body">
                            @if($product->categories && $product->categories->count() > 0)
                                <div class="listing-card__meta" style="margin-bottom: 8px;">
                                    @php $displayCategory = $product->categories->first(); @endphp
                                    <a href="{{ $displayCategory->frontend_url }}" class="badge">{{ $displayCategory->name }}</a>
                                </div>
                            @endif
                            <h3 class="listing-card__title">
                                <a href="{{ $product->frontend_url }}">{{ $product->name }}</a>
                            </h3>
                            @if(!empty($product->short_description))
                                <p class="listing-card__excerpt" style="margin-bottom: 12px;">{{ Str::limit(strip_tags($product->short_description), 120) }}</p>
                            @endif
                            @if(!empty($product->formatted_price))
                                <div class="listing-card__price font-semibold" style="color: var(--geist-success, #10b981);">
                                    {{ $product->formatted_price }}
                                </div>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            @if(is_object($products) && method_exists($products, 'links'))
                <div style="margin-top: 1.5rem;">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    @endif

    {{-- Posts Section --}}
    @if(count($posts) > 0)
        <div style="margin-bottom: 3rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h2 style="font-size: 1.25rem; font-weight: 700; display: flex; align-items: center; gap: 0.5rem;">
                    <span>📰</span> {{ _l('Articles & Content') }}
                </h2>
                @if($target === 'all' && is_object($posts) && method_exists($posts, 'hasMorePages') && $posts->hasMorePages())
                    <a href="{{ url('/search') }}?search={{ urlencode($search) }}&target=posts" class="btn btn-sm btn-link">
                        {{ _l('View All Articles') }} &rarr;
                    </a>
                @endif
            </div>

            <div class="listing-container is-grid" style="--listing-columns: 3">
                @foreach($posts as $post)
                    <article class="listing-card">
                        <a href="{{ $post->frontend_url }}" class="listing-card__image">
                            @php $thumbnail = $post->featured_image_url; @endphp
                            @if($thumbnail)
                                <img src="{{ $thumbnail }}" alt="{{ $post->title }}" {!! media_lazy_attr() !!}>
                            @else
                                <div class="listing-card__no-image">
                                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/>
                                        <polyline points="21 15 16 10 5 21"/>
                                    </svg>
                                </div>
                            @endif
                        </a>
                        <div class="listing-card__body">
                            <h3 class="listing-card__title">
                                <a href="{{ $post->frontend_url }}">{{ $post->title }}</a>
                            </h3>
                            @if($post->excerpt || $post->content_html)
                                <p class="listing-card__excerpt">
                                    {{ Str::limit(strip_tags($post->excerpt ?: $post->content_html), 120) }}
                                </p>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            @if(is_object($posts) && method_exists($posts, 'links'))
                <div style="margin-top: 1.5rem;">
                    {{ $posts->links() }}
                </div>
            @endif
        </div>
    @endif
</div>
@endsection
