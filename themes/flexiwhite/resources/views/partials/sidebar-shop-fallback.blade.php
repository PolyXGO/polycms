<div class="sidebar-fallback">
    <!-- Categories Widget -->
    <div class="card" style="padding: 1.5rem; border: 1px solid var(--geist-accents-2); border-radius: var(--radius); margin-bottom: 2rem;">
        <h3 style="font-size: 1.125rem; margin-bottom: 1rem; color: var(--geist-foreground);">{{ _l('Product Categories') }}</h3>
        <ul style="list-style: none; padding: 0;">
            @php
                $fallbackCategories = \App\Models\Category::where('type', 'product')->take(5)->get();
            @endphp
            @forelse($fallbackCategories as $cat)
                <li style="margin-bottom: 0.5rem; border-bottom: 1px solid var(--geist-accents-2); padding-bottom: 0.5rem;">
                    <a href="{{ $cat->frontend_url }}" style="color: var(--geist-accents-6); transition: color 0.2s;" onmouseover="this.style.color='var(--geist-foreground)'" onmouseout="this.style.color='var(--geist-accents-6)'">{{ $cat->name }}</a>
                </li>
            @empty
                <li style="color: var(--geist-accents-4); font-size: 0.875rem;">{{ _l('No categories found.') }}</li>
            @endforelse
        </ul>
    </div>

    <!-- Recent Products Widget -->
    <div class="card" style="padding: 1.5rem; border: 1px solid var(--geist-accents-2); border-radius: var(--radius);">
        <h3 style="font-size: 1.125rem; margin-bottom: 1rem; color: var(--geist-foreground);">{{ _l('Latest Products') }}</h3>
        <ul style="list-style: none; padding: 0;">
            @php
                $fallbackProducts = \App\Models\Product::where('status', 'published')->where('locale', app()->getLocale())->where('slug', 'not like', 'test-%')->latest()->take(3)->get();
            @endphp
            @forelse($fallbackProducts as $fp)
                <li style="margin-bottom: 1rem; display: flex; gap: 0.75rem; align-items: center;">
                    @php $fpImageUrl = $fp->featured_image_url; @endphp
                    @if(!empty($fpImageUrl))
                        <img src="{{ $fpImageUrl }}" alt="{{ $fp->name }}" style="width: 48px; height: 48px; object-fit: cover; border-radius: 4px;" {!! media_lazy_attr() !!}>
                    @else
                        <div style="width: 48px; height: 48px; background-color: var(--geist-accents-2); border-radius: 4px;"></div>
                    @endif
                    <div>
                        <a href="{{ $fp->frontend_url }}" style="font-weight: 500; font-size: 0.875rem; color: var(--geist-foreground); display: block; margin-bottom: 0.125rem;">{{ $fp->name }}</a>
                        <span style="font-size: 0.875rem; color: var(--geist-accents-5);">{{ format_currency($fp->price) }}</span>
                    </div>
                </li>
            @empty
                <li style="color: var(--geist-accents-4); font-size: 0.875rem;">{{ _l('No products found.') }}</li>
            @endforelse
        </ul>
    </div>
</div>
