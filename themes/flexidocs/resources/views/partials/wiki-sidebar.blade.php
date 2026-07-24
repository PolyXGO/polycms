@php
    $wiki_category = $wiki_category ?? null;
    $current_post_id = $current_post_id ?? null;
@endphp

@if($wiki_category)
<nav class="space-y-8">
    @php
        // Try to load children and their posts if not eager loaded
        $groups = $wiki_category->children()->with('posts.meta')->get();
        if ($groups->isEmpty()) {
            // Fallback: If no children, treat current category as the only group
            $groups = collect([$wiki_category]);
        }
    @endphp

    @foreach($groups as $group)
        <div class="wiki-group">
            @if($groups->count() > 1 || $group->name !== $wiki_category->name)
                <h3 class="font-semibold text-slate-900 dark:text-white uppercase tracking-wider text-xs mb-3 flex items-center gap-2">
                    {{ $group->name }}
                </h3>
            @endif
            
            @php
                $posts = $group->posts()->published()->orderBy('order')->orderBy('created_at', 'asc')->get();
            @endphp
            
            @if($posts->isNotEmpty())
                <ul class="space-y-1">
                    @foreach($posts as $post)
                        @php
                            $isActive = $current_post_id == $post->id || theme_is_menu_active(['url' => $post->frontend_url]);
                            $subtitle = collect($post->meta)->firstWhere('meta_key', 'flexidocs_subtitle')?->meta_value 
                                        ?? $post->getMeta('flexidocs_subtitle');
                            // Use hook-aware frontend_url (returns /docs/category/post-slug)
                            $articleUrl = $post->frontend_url;
                        @endphp
                        <li>
                            <a href="{{ $articleUrl }}" 
                               class="group block px-3 py-2 rounded-md text-sm transition-colors {{ $isActive ? 'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400 font-medium' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/50 dark:hover:text-white' }}">
                                <span class="block">{{ $post->title }}</span>
                                @if($subtitle)
                                    <span class="block text-xs mt-0.5 {{ $isActive ? 'text-blue-400 dark:text-blue-500' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-500 dark:group-hover:text-slate-400' }}">
                                        {{ $subtitle }}
                                    </span>
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-xs text-slate-400 italic px-3">{{ _l('No articles yet.') }}</p>
            @endif
        </div>
    @endforeach
</nav>
@endif
