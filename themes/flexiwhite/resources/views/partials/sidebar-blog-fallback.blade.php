<div class="sidebar-fallback">
    <!-- Categories Widget -->
    <div class="card" style="padding: 1.5rem; border: 1px solid var(--geist-accents-2); border-radius: var(--radius); margin-bottom: 2rem;">
        <h3 style="font-size: 1.125rem; margin-bottom: 1rem; color: var(--geist-foreground);">{{ _l('Categories') }}</h3>
        <ul style="list-style: none; padding: 0;">
            @php
                $fallbackCategories = \App\Models\Category::where('type', 'post')->take(5)->get();
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

    <!-- Recent Posts Widget -->
    <div class="card" style="padding: 1.5rem; border: 1px solid var(--geist-accents-2); border-radius: var(--radius);">
        <h3 style="font-size: 1.125rem; margin-bottom: 1rem; color: var(--geist-foreground);">{{ _l('Recent Posts') }}</h3>
        <ul style="list-style: none; padding: 0;">
            @php
                $fallbackPosts = \App\Models\Post::where('status', 'published')->latest()->take(3)->get();
            @endphp
            @forelse($fallbackPosts as $fp)
                <li style="margin-bottom: 1rem;">
                    <a href="{{ $fp->frontend_url }}" style="font-weight: 500; font-size: 0.875rem; color: var(--geist-foreground); display: block; margin-bottom: 0.25rem;">{{ $fp->title }}</a>
                    <span style="font-size: 0.75rem; color: var(--geist-accents-5);">{{ format_post_date($fp->published_at) }}</span>
                </li>
            @empty
                <li style="color: var(--geist-accents-4); font-size: 0.875rem;">{{ _l('No recent posts.') }}</li>
            @endforelse
        </ul>
    </div>
</div>
