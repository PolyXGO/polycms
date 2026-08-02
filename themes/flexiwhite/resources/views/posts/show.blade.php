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
            $displayCategory = $post->primary_category ?? $post->categories->sortByDesc('depth')->first();
            foreach (collect($displayCategory->breadcrumb ?? [])->filter() as $cat) {
                $breadcrumbs[] = ['label' => $cat->name, 'url' => $cat->frontend_url];
            }
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
                        @php $displayCategory = $post->categories->sortByDesc('depth')->first(); @endphp
                        <a href="{{ $displayCategory->frontend_url }}" class="badge">{{ $displayCategory->name }}</a>
                    @endif
                    
                    @if($post->user)
                        <span style="margin-left: 0.5rem;">{{ _l('By') }} <a href="{{ route('authors.show', $post->user) }}">{{ $post->user->name }}</a></span>
                    @endif

                    @if(theme_get_option('flexiwhite_post_show_date', 'show') === 'show')
                        @if($post->categories->count() > 0 || $post->user) <span style="margin: 0 0.5rem; color: var(--geist-accents-3);">&bull;</span> @endif
                        <span>{{ format_post_date($post->published_at ?? $post->created_at) }}</span>
                    @endif
                </div>

                <h1 class="post-title" style="margin-bottom: 0.25rem;">{{ $post->title }}</h1>
                <div style="text-align: center; margin-bottom: 1rem;">
                    {!! \App\Facades\Hook::doAction('theme.post.single.after_title', $post) !!}
                </div>

                @if($post->excerpt)
                    <p class="post-excerpt">{{ $post->excerpt }}</p>
                @endif
            </header>

            <!-- Cover Image -->
            @if(theme_get_option('flexiwhite_post_show_featured_image', 'show') === 'show')
                @php $thumbnail = $post->featured_image_url; @endphp
                @if($thumbnail)
                    <img src="{{ $thumbnail }}" alt="{{ $post->title }}" class="post-cover" {!! media_lazy_attr() !!}>
                @endif
            @endif

            <!-- Content -->
            <article class="prose">
                {!! filter_content_lazy_images(render_dynamic_blocks($renderedContent ?? ($post->content_html ?? ''))) !!}
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
