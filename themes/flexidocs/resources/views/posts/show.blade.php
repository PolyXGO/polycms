@extends('theme.flexidocs::layouts.wiki')

@section('title', $post->title . ' - ' . (function_exists('get_theme_mod') ? get_theme_mod('site_title', 'Help Desk') : 'Help Desk'))
@section('description', $post->meta_description ?? (function_exists('the_excerpt') ? the_excerpt($post, 160) : strip_tags($post->excerpt)))

@section('sidebar')
    @php
        // Find the root category for this post to act as the wiki navigation context
        $wiki_category = null;
        $category = $post->categories->first();
        if ($category) {
            // Usually in docs setup, the root category has the 'wiki-docs' or 'categories.show' template
            $current = $category;
            while ($current) {
                if (isFlexidocsTemplate($current->template_theme) || !$current->parent_id) {
                    $wiki_category = $current;
                    if (isFlexidocsTemplate($current->template_theme)) {
                        break;
                    }
                }
                $current = $current->parent;
            }
        }
    @endphp

    @include('theme.flexidocs::partials.wiki-sidebar', [
        'wiki_category' => $wiki_category ?? $category,
        'current_post_id' => $post->id
    ])
@endsection

@section('content')
    @include('theme.flexidocs::partials.wiki-post-content', ['post' => $post])
@endsection
