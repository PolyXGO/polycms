@extends('layouts.app')

@section('title', $post->meta_title ?? $post->title)
@section('description', $post->meta_description ?? the_excerpt($post, 150))

@section('breadcrumb')
    @php
        $postArchiveUrl = theme_permalink_url('posts', '', 'archive');
        $breadcrumbs = [
            ['label' => _l('Home'), 'url' => url('/')],
            ['label' => _l('Blog'), 'url' => $postArchiveUrl],
        ];
        if ($post->categories->count() > 0) {
            $displayCategory = $post->categories->whereNotNull('parent_id')->first() ?? $post->categories->first();
            $breadcrumbs[] = ['label' => $displayCategory->name, 'url' => null];
        }
        $breadcrumbs = \App\Facades\Hook::applyFilters('theme.breadcrumbs.post', $breadcrumbs, $post);
    @endphp
    @include('partials.breadcrumb', ['items' => $breadcrumbs])
@endsection

@section('content')
<div class="container section">
    <div class="grid-sidebar">
        
        <!-- Main Content Column -->
        <div class="reading-container">

            <!-- Post Header -->
            <header class="post-header">
                <div class="card-meta" style="justify-content: center; margin-bottom: 1.5rem;">
                    @if($post->categories->count() > 0)
                        @php $displayCategory = $post->categories->whereNotNull('parent_id')->first() ?? $post->categories->first(); @endphp
                        <a href="{{ $displayCategory->frontend_url }}" class="badge">{{ $displayCategory->name }}</a>
                    @endif
                    <span>{{ format_post_date($post->published_at) }}</span>
                    @if($post->user)
                        <span>&bull;</span>
                        <span>{{ _l('By') }} {{ $post->user->name }}</span>
                    @endif
                </div>

                <h1 class="post-title">{{ $post->title }}</h1>

                @if($post->excerpt)
                    <p class="post-excerpt">{{ $post->excerpt }}</p>
                @endif
            </header>

            <!-- Cover Image -->
            @php $thumbnail = $post->featured_image ?: get_default_post_image($post); @endphp
            @if($thumbnail)
                <img src="{{ $thumbnail }}" alt="{{ $post->title }}" class="post-cover" loading="lazy">
            @endif

            <!-- Content -->
            <article class="prose">
                {!! render_dynamic_blocks($post->content_html) !!}
            </article>

            <!-- Footer: Categories & Tags -->
            <footer style="margin-top: 4rem; padding-top: 2rem; border-top: 1px solid var(--geist-accents-2);">
                @if($post->categories->count() > 0)
                    <div style="margin-bottom: 1.5rem;">
                        <h3 style="font-size: 0.875rem; text-transform: uppercase; color: var(--geist-accents-5); margin-bottom: 0.75rem;">{{ _l('Categories') }}</h3>
                        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                            @foreach($post->categories as $category)
                                <a href="{{ $category->frontend_url }}" class="badge">{{ $category->name }}</a>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($post->tags->count() > 0)
                    <div>
                        <h3 style="font-size: 0.875rem; text-transform: uppercase; color: var(--geist-accents-5); margin-bottom: 0.75rem;">{{ _l('Tags') }}</h3>
                        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                            @foreach($post->tags as $tag)
                                <a href="{{ $tag->frontend_url }}" style="color: var(--geist-success); font-size: 0.875rem;">#{{ $tag->name }}</a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </footer>
        </div>

        <!-- Sidebar -->
        <aside>
            @if(theme_widget_area_has_content('sidebar_blog'))
                @include('partials.widget-area', [
                    'key' => 'sidebar_blog',
                    'class' => 'sidebar-widget-stack',
                    'title' => _l('Blog Sidebar'),
                ])
            @else
                @include('partials.sidebar-blog-fallback')
            @endif
        </aside>
        
    </div>
</div>
@endsection
