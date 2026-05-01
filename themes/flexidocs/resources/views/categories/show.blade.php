@extends('theme.flexidocs::layouts.wiki')

@if(isset($activePost))
    @section('title', optional($activePost)->meta_title ?: optional($activePost)->title . ' - ' . (function_exists('get_theme_mod') ? get_theme_mod('site_title', 'Help Desk') : 'Help Desk'))
    @section('description', optional($activePost)->meta_description ?: (function_exists('the_excerpt') ? the_excerpt($activePost, 160) : strip_tags(optional($activePost)->excerpt)))
@else
    @section('title', (optional($category)->meta_title ?: (optional($category)->name ?: 'Wiki')) . ' - ' . (function_exists('get_theme_mod') ? get_theme_mod('site_title', 'Help Desk') : 'Help Desk'))
    @section('description', optional($category)->meta_description ?: (optional($category)->summary ?: ''))
@endif

@section('sidebar')
    @include('theme.flexidocs::partials.wiki-sidebar', ['wiki_category' => $category ?? null])
@endsection

@if(isset($activePost) && !empty($activePost->template_theme) && in_array($activePost->template_theme, ['flexidocs::posts.iframe', 'flexidocs::posts.iframe-full']))
    @section('wrapper_class', 'h-full w-full p-0')
@endif

@section('content')
    @if(isset($activePost))
        @if(!empty($activePost->template_theme) && in_array($activePost->template_theme, ['flexidocs::posts.iframe', 'flexidocs::posts.iframe-full']))
            @php
                $iframeUrl = $activePost->getMeta('iframe_url') ?? '';
            @endphp
            <div class="h-full w-full flex flex-col">
                @if($iframeUrl)
                    <iframe 
                        src="{{ $iframeUrl }}" 
                        title="{{ $activePost->title ?? 'Post' }}"
                        class="w-full h-full border-0 min-h-[calc(100vh-64px)]"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen
                        loading="lazy"
                    ></iframe>
                @else
                    <div class="flex items-center justify-center p-12 text-gray-500 dark:text-gray-400 text-center w-full min-h-[50vh]">
                        <div class="space-y-4">
                            <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <p class="text-lg font-medium">{{ _l('Chưa cấu hình URL Iframe') }}</p>
                            <p class="text-sm mt-2">{{ _l('Vui lòng cập nhật trường Iframe URL trong phần chỉnh sửa bài viết (Tab Theme Template).') }}</p>
                        </div>
                    </div>
                @endif
            </div>
        @else
            @include('theme.flexidocs::partials.wiki-post-content', ['post' => $activePost])
            
            @section('toc')
            <nav class="wiki-toc text-sm">
                <ul class="space-y-2.5 text-slate-600 dark:text-slate-400">
                    <template x-for="(heading, index) in headings" :key="heading.id">
                        <li :class="{ 'ml-4': heading.level === 3, 'ml-8': heading.level === 4, 'font-medium text-slate-900 dark:text-slate-200': activeId === heading.id }">
                            <a @click.prevent="scrollToHeading(heading.id, index)" :href="'#' + heading.id" x-text="heading.text" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors" :class="{'text-blue-600 dark:text-blue-400': activeId === heading.id}"></a>
                        </li>
                    </template>
                    <li x-show="headings.length === 0" class="text-slate-400 italic">{{ _l('No headings in document') }}</li>
                </ul>
            </nav>
            @endsection
        @endif
    @else
        <div class="wiki-docs-landing">
            {{-- Category Header --}}
            <div class="wiki-docs-header">
                <h1 class="wiki-docs-header__title">
                    {{ $category->name ?? '' }}
                </h1>
                @if(!empty($category->summary))
                    <p class="wiki-docs-header__summary">{{ $category->summary }}</p>
                @endif
                @if(!empty($category->description))
                    <div class="wiki-docs-header__description">{!! $category->description !!}</div>
                @endif
            </div>

            {{-- Direct posts (in this category, not in children) --}}
            @if(isset($directPosts) && $directPosts->isNotEmpty())
                <div class="mb-8">
                    <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-3">{{ $category->name ?? _l('General') }}</h2>
                    <ul class="space-y-2">
                        @foreach($directPosts as $post)
                            <li>
                                <a href="{{ $post->frontend_url }}" class="text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                    {{ $post->title }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Child Categories as Groups --}}
            @if(isset($childCategories) && $childCategories->isNotEmpty())
                @foreach($childCategories as $childCategory)
                    <div class="mb-8">
                        <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-3">
                            <a href="{{ $childCategory->frontend_url }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">{{ $childCategory->name }}</a>
                            @if($childCategory->groupPosts)
                                <span class="text-xs font-normal text-slate-400 dark:text-slate-500 ml-1">({{ $childCategory->groupPosts->count() }})</span>
                            @endif
                        </h2>
                        @if($childCategory->groupPosts && $childCategory->groupPosts->isNotEmpty())
                            <ul class="space-y-2">
                                @foreach($childCategory->groupPosts as $post)
                                    <li>
                                        <a href="{{ $post->frontend_url }}" class="text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                            {{ $post->title }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-sm text-slate-400 dark:text-slate-500 italic">{{ _l('No articles in this section yet.') }}</p>
                        @endif
                    </div>
                @endforeach
            @endif

            {{-- Empty state --}}
            @if((!isset($childCategories) || $childCategories->isEmpty()) && (!isset($directPosts) || $directPosts->isEmpty()))
                <div class="py-12 text-center">
                    <p class="text-slate-500 dark:text-slate-400">{{ _l('No content available. This documentation section is being prepared.') }}</p>
                </div>
            @endif
        </div>
    </div>
    @endif
@endsection

