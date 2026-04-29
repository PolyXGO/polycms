@php
    $galleryItems = $galleries ?? $product->media;
@endphp
<div class="product-gallery">
    <div class="product-gallery-viewport">
        @if($galleryItems && $galleryItems->count() > 1)
            <button type="button" class="product-gallery-nav prev" data-action="prev" aria-label="Previous image">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button type="button" class="product-gallery-nav next" data-action="next" aria-label="Next image">
                <i class="fas fa-chevron-right"></i>
            </button>
        @endif

        @if($galleryItems && $galleryItems->count() > 0)
            @foreach($galleryItems as $index => $media)
                <div class="product-gallery-slide {{ $index === 0 ? 'active' : '' }}" data-index="{{ $index }}">
                    <img src="{{ $media->url ?? '' }}" alt="{{ $product->name }}">
                </div>
            @endforeach
        @else
            <div class="product-gallery-slide active">
                <div class="product-gallery-placeholder">
                    <i class="fas fa-image" style="font-size: 64px;"></i>
                </div>
            </div>
        @endif
    </div>

    @if($galleryItems && $galleryItems->count() > 1)
        <div class="product-gallery-thumbs-wrapper">
            <button type="button" class="product-gallery-thumbs-nav prev" data-action="thumbs-prev" aria-label="Scroll thumbnails left">
                <i class="fas fa-chevron-left"></i>
            </button>
            <div class="product-gallery-thumbs">
                @foreach($galleryItems as $index => $media)
                    <button type="button" class="product-gallery-thumb {{ $index === 0 ? 'active' : '' }}" data-target="{{ $index }}" aria-label="View image {{ $index + 1 }}">
                        <img src="{{ $media->url ?? '' }}" alt="{{ $product->name }} thumb">
                    </button>
                @endforeach
            </div>
            <button type="button" class="product-gallery-thumbs-nav next" data-action="thumbs-next" aria-label="Scroll thumbnails right">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    @endif
</div>
