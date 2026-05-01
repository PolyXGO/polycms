<article class="prose prose-slate dark:prose-invert max-w-none mt-2 lg:mt-4">
    <div class="wiki-content prose-headings:scroll-mt-24 prose-a:text-blue-600 dark:prose-a:text-blue-400 prose-a:no-underline hover:prose-a:underline prose-img:rounded-xl">
        <div class="mb-6 pb-4 border-b border-slate-200 dark:border-slate-700">
            @php
                $direct_category = $post->categories->sortByDesc('parent_id')->first() ?? $post->categories->first();
                $root_category = null;
                if ($direct_category) {
                    $current = $direct_category;
                    while ($current) {
                        if (!$current->parent_id) {
                            $root_category = $current;
                            break;
                        }
                        $current = $current->parent;
                    }
                }
            @endphp
            
            @if($root_category)
                <nav class="flex flex-wrap text-sm text-slate-500 dark:text-slate-400 mb-4 items-center gap-y-1">
                    <a href="{{ $root_category->frontend_url ?? url('/categories/' . $root_category->slug) }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors shrink-0">
                        {{ $root_category->name }}
                    </a>
                    
                    @if($direct_category && $direct_category->id !== $root_category->id)
                        <svg class="w-4 h-4 mx-2 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        <a href="{{ $direct_category->frontend_url ?? url('/categories/' . $direct_category->slug) }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors shrink-0">
                            {{ $direct_category->name }}
                        </a>
                    @endif

                    <svg class="w-4 h-4 mx-2 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    <span class="text-slate-700 dark:text-slate-300 font-medium truncate max-w-[150px] sm:max-w-xs md:max-w-md shrink-0">{{ $post->title }}</span>
                </nav>
            @endif

            <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white mb-4 leading-tight">
                {{ $post->title }}
            </h1>
            
            @php
                $subtitle = collect($post->meta)->firstWhere('meta_key', 'flexidocs_subtitle')?->meta_value 
                            ?? $post->getMeta('flexidocs_subtitle');
            @endphp
            
            @if($subtitle)
                <p class="text-xl text-slate-600 dark:text-slate-400 font-medium">
                    {{ $subtitle }}
                </p>
            @endif
            
            <div class="mt-6 flex items-center text-sm text-slate-500 dark:text-slate-400">
                <span class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    {{ _l('Last updated on') }} {{ format_post_date($post->updated_at, 'M j, Y') }}
                </span>
            </div>
        </div>

        {!! $post->content_html !!}
    </div>
    
    <footer class="mt-16 pt-8 border-t border-slate-200 dark:border-slate-700"
            x-data="{ voted: false, voteType: null }"
    >
        <div class="text-sm text-slate-500 dark:text-slate-400 flex items-center gap-3">
            <template x-if="!voted">
                <span class="flex items-center gap-3">
                    {{ _l('Was this article helpful?') }}
                    <button @click="
                        fetch('/api/v1/content-votes', {
                            method: 'POST',
                            headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
                            body: JSON.stringify({voteable_type: 'post', voteable_id: {{ $post->id }}, type: 'helpful', source: 'flexidocs'})
                        }).then(() => { voted = true; voteType = 'helpful'; })
                    " class="px-3 py-1 rounded-full border border-emerald-300 dark:border-emerald-600 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 font-medium transition-colors flex items-center">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"></path></svg>
                        {{ _l('Yes') }}
                    </button>
                    <button @click="
                        fetch('/api/v1/content-votes', {
                            method: 'POST',
                            headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
                            body: JSON.stringify({voteable_type: 'post', voteable_id: {{ $post->id }}, type: 'not_helpful', source: 'flexidocs'})
                        }).then(() => { voted = true; voteType = 'not_helpful'; })
                    " class="px-3 py-1 rounded-full border border-slate-300 dark:border-slate-600 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 font-medium transition-colors flex items-center">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14H5.236a2 2 0 01-1.789-2.894l3.5-7A2 2 0 018.736 3h4.018a2 2 0 01.485.06l3.76.94m-7 10v5a2 2 0 002 2h.096c.5 0 .905-.405.905-.904 0-.714.211-1.412.608-2.006L17 13V4m-7 10h2m5-10h2a2 2 0 012 2v6a2 2 0 01-2 2h-2.5"></path></svg>
                        {{ _l('No') }}
                    </button>
                </span>
            </template>
            <template x-if="voted">
                <span class="text-emerald-600 dark:text-emerald-400 font-medium" x-transition>
                    {{ _l('Thank you for your feedback!') }}
                </span>
            </template>
        </div>
    </footer>
</article>

