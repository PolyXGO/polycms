<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Frontend\FrontendController;
use App\Models\LayoutAsset;
use App\Models\Post;
use App\Services\ContentRenderer;
use App\Services\TemplateResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class PageController extends FrontendController
{
    public function __construct(
        protected TemplateResolver $templateResolver,
    ) {}
    /**
     * Display a static page
     */
    public function show(string $slug, Request $request): View|RedirectResponse
    {
        $settingsService = app(\App\Services\SettingsService::class);
        $permalinks = $settingsService->getPermalinkStructure();
        $postsSingleBase = trim($permalinks['posts']['single'] ?? 'posts', '/');

        // Allow 'page' always. Allow 'post' only if its base is empty.
        $allowedTypes = ['page'];
        if ($postsSingleBase === '') {
            $allowedTypes[] = 'post';
        }

        $query = Post::with(['user', 'categories', 'tags'])
            ->where('slug', $slug)
            ->whereIn('type', $allowedTypes);

        // Check if user is admin - allow viewing draft posts/pages
        $isAdmin = $this->isAdmin($request);

        if (!$isAdmin) {
            // Non-admin users can only see published content
            $query->where('status', 'published');
        }

        $post = $query->firstOrFail();

        // Redirect to home if this page is set as the static homepage
        if ($post->type === 'page') {
            $showOnFront = $settingsService->get('reading_show_on_front', 'posts');
            if ($showOnFront === 'page') {
                $homepageId = $settingsService->get('reading_page_on_front');
                if ($homepageId && (int)$homepageId === $post->id) {
                    return redirect()->route('home', [], 301);
                }
            }
        }

        // Increment views
        if ($post->status === 'published' || $isAdmin) {
            $post->increment('views');
        }

        if ($post->type === 'post') {
            $data = [
                'post' => $post,
                'renderedContent' => $this->resolveRenderedContent($post),
            ];
            $data = \App\Facades\Hook::applyFilters('theme.view.data', $data, 'posts.show');

            $templateTheme = $post->template_theme ?? null;
            $viewName = $this->templateResolver->resolve('posts.show', $templateTheme, 'posts');
            $data['__templateTheme'] = $templateTheme;
            return view($viewName, $data);
        } else {
            $data = [
                'page' => $post,
                'renderedContent' => $this->resolveRenderedContent($post),
            ];
            $data = \App\Facades\Hook::applyFilters('theme.view.data', $data, 'pages.show');

            $templateTheme = $post->template_theme ?? null;
            $viewName = $this->templateResolver->resolve('pages.show', $templateTheme, 'pages');
            $data['__templateTheme'] = $templateTheme;
            return view($viewName, $data);
        }
    }

    protected function resolveRenderedContent(Post $page): string
    {
        if (($page->layout ?? 'default') !== 'landing') {
            return (string) ($page->content_html ?? '');
        }

        $blocks = $page->content_raw;

        if ((empty($blocks) || !is_array($blocks)) && $page->layout_template_id && $this->canUseLayoutTemplates()) {
            $template = LayoutAsset::query()
                ->templates()
                ->find($page->layout_template_id);
            $blocks = $template?->content_raw;
        }

        if (empty($blocks) || !is_array($blocks)) {
            return (string) ($page->content_html ?? '');
        }

        return app(ContentRenderer::class)
            ->setContext([
                'page' => $page,
                'post' => $page,
            ])
            ->render($blocks);
    }

    protected function canUseLayoutTemplates(): bool
    {
        return Schema::hasTable('layout_assets')
            && Schema::hasTable('posts')
            && Schema::hasColumn('posts', 'layout_template_id');
    }
}
