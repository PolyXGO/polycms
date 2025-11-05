<?php

declare(strict_types=1);

namespace App\Actions;

use App\Facades\Hook;
use App\Models\Post;
use App\Services\ContentRenderer;
use Illuminate\Support\Facades\DB;

class UpdatePost
{
    public function __construct(
        protected ContentRenderer $renderer
    ) {}

    /**
     * Update an existing post
     */
    public function execute(Post $post, array $data, ?array $categoryIds = null, ?array $tagIds = null): Post
    {
        return DB::transaction(function () use ($post, $data, $categoryIds, $tagIds) {
            // Apply filters before updating
            $data = Hook::applyFilters('post.update.data', $data, $post);

            // Handle content: prioritize content_html (from Tiptap), fallback to rendering blocks
            if (isset($data['content_html']) && !empty($data['content_html'])) {
                // Use HTML content directly from Tiptap editor
                // content_html is already set, no need to render
            } elseif (isset($data['content_raw'])) {
                // Backward compatibility: render from blocks if content_html is not provided
                $contentRaw = $data['content_raw'];
                // Handle both formats: { blocks: [...] } or direct array
                if (is_array($contentRaw)) {
                    $blocks = isset($contentRaw['blocks']) ? $contentRaw['blocks'] : $contentRaw;
                } else {
                    $blocks = [];
                }
                
                if (!empty($blocks) && is_array($blocks)) {
                    $data['content_html'] = $this->renderer->render($blocks);
                }
            }

            $post->update($data);

            // Sync categories if provided
            if ($categoryIds !== null) {
                $post->categories()->sync($categoryIds);
            }

            // Sync tags if provided
            if ($tagIds !== null) {
                $post->tags()->sync($tagIds);
            }

            // Fire action hook
            Hook::doAction('post.saved', $post);

            return $post->load(['user', 'categories', 'tags']);
        });
    }
}
