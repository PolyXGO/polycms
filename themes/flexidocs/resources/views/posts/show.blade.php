@extends('theme.flexidocs::layouts.wiki')

@section('title', $post->title . ' - ' . (function_exists('get_theme_mod') ? get_theme_mod('site_title', 'Help Desk') : 'Help Desk'))
@section('description', $post->meta_description ?? (function_exists('the_excerpt') ? the_excerpt($post, 160) : strip_tags($post->excerpt)))

@section('sidebar')
    @php
        // Find the appropriate category context for the wiki sidebar.
        // Strategy: walk up to find the flexidocs/root ancestor, then step down
        // to its direct child that is in the post's ancestry chain.
        // This gives a focused 2-level sidebar instead of the entire root tree.
        $wiki_category = null;
        $category = $post->primary_category ?? $post->categories->first();
        if ($category) {
            $ancestors = $category->breadcrumb;
            $flexidocs_root = $ancestors->first(function ($cat) {
                return (function_exists('isFlexidocsTemplate') && isFlexidocsTemplate($cat->template_theme ?? '')) || !$cat->parent_id;
            }) ?? $ancestors->first();

            if ($flexidocs_root) {
                $scoped = $ancestors->first(function ($cat) use ($flexidocs_root) {
                    return $cat->parent_id == $flexidocs_root->id;
                });
                $wiki_category = $scoped ?? $flexidocs_root;
            } else {
                $wiki_category = $category;
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
