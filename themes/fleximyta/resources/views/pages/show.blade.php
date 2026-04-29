@extends(($page->layout ?? 'default') === 'landing' ? 'layouts.landing' : 'layouts.app')

@section('title', $page->title ?? 'Page')

@section('description', $page->meta_description ?? $page->excerpt ?? '')

@section('content')
@if(($page->layout ?? 'default') === 'landing')
    {!! render_dynamic_blocks($renderedContent ?? ($page->content_html ?? '')) !!}
@else
    @php
        $layout = $page->layout ?? 'default';
        $containerClass = $layout === 'fullwidth' ? 'container-wide' : 'container';
        // Only show gallery if layout is single-column AND media exists (graceful degradation)
        $showGallery = $layout === 'single-column' && !empty($page->media) && count($page->media) > 0;
    @endphp

@if(\App\Facades\Hook::applyFilters('theme.show_page_header', true, $page))
<section class="page-header-section">
    <div class="{{ $containerClass }}">
        <div class="page-header-content">
            <h1 class="page-header-title">
                {{ $page->title }}
            </h1>
            @if(!empty($page->excerpt))
                <p class="page-header-excerpt">
                    {{ $page->excerpt }}
                </p>
            @endif
        </div>
    </div>
</section>
@endif

<section class="page-content-section">
    <div class="{{ $containerClass }}">
        @if($showGallery)
            <div class="product-gallery-top" style="margin-bottom: 50px; max-width: 1000px; margin-left: auto; margin-right: auto;">
                 @include('products.partials.gallery', ['product' => $page])
            </div>
        @endif

        <article class="page-article">
            @if(!empty($page->featured_image) && ($page->show_featured_image ?? true))
                <img src="{{ $page->featured_image }}" alt="{{ $page->title }}" style="width: 100%; height: 450px; object-fit: cover; display: block; border-radius: var(--radius);">
            @endif

            <div style="padding: 3rem 0;">
                <div class="prose">
                    {!! render_dynamic_blocks($page->content_html ?? '') !!}
                </div>
            </div>
        </article>
    </div>
</section>
@endif
@endsection
