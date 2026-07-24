<div class="widget widget-products">
    @if(!empty($title))
        <h3 class="widget-title">{{ $title }}</h3>
    @endif
    @php
        $showMedia = (bool) ($config['show_media'] ?? true);
        $showPrice = (bool) ($config['show_price'] ?? true);
    @endphp
    <ul class="widget-list" style="list-style: none; padding: 0; margin: 0;">
        @foreach($products as $product)
            @php
                $url = theme_permalink_url('products', $product->slug, 'single');
                $featuredImageUrl = $product->thumbnail_url;
            @endphp
            <li class="widget-product-item" style="display: flex; gap: 12px; margin-bottom: 12px; align-items: center;">
                @if($showMedia)
                    <a href="{{ $url }}" class="widget-product-image" style="width: 50px; height: 50px; flex-shrink: 0; display: block; border-radius: 6px; overflow: hidden; background: #f1f5f9; border: 1px solid #e2e8f0;">
                        @if(!empty($featuredImageUrl))
                            <img src="{{ $featuredImageUrl }}" alt="{{ $product->name }}" style="width: 100%; height: 100%; object-fit: cover;" {!! media_lazy_attr() !!}>
                        @else
                            <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: #94a3b8; background: #f8fafc;">
                                <i class="far fa-image" style="font-size: 1.2rem;"></i>
                            </div>
                        @endif
                    </a>
                @endif
                <div class="widget-product-info" style="flex-grow: 1; min-width: 0;">
                    <a href="{{ $url }}" class="widget-product-name" style="font-size: 0.9rem; font-weight: 600; color: var(--geist-foreground, #1e293b); text-decoration: none; display: block; margin-bottom: 4px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                        {{ $product->name }}
                    </a>
                    @if($showPrice)
                        <div class="widget-product-price" style="font-size: 0.85rem;">
                            @if($product->isOnSale())
                                <span class="price-old" style="text-decoration: line-through; color: #94a3b8; margin-right: 6px;">{{ format_currency($product->price) }}</span>
                                <span class="price-new" style="color: #ef4444; font-weight: 700;">{{ format_currency($product->sale_price) }}</span>
                            @elseif($product->price !== null)
                                <span class="price-normal" style="color: var(--geist-accents-6, #475569); font-weight: 600;">{{ format_currency($product->price) }}</span>
                            @endif
                        </div>
                    @endif
                </div>
            </li>
        @endforeach
    </ul>
</div>
