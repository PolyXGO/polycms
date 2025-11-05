<?php

declare(strict_types=1);

namespace App\Actions;

use App\Facades\Hook;
use App\Models\Post;
use App\Services\ContentRenderer;
use Illuminate\Support\Facades\DB;

class CreatePost
{
    public function __construct(
        protected ContentRenderer $renderer
    ) {}

    /**
     * Create a new post
     */
    public function execute(array $data, array $categoryIds = [], array $tagIds = []): Post
    {
        return DB::transaction(function () use ($data, $categoryIds, $tagIds) {
            // Apply filters before creating
            $data = Hook::applyFilters('post.create.data', $data);

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

            $post = Post::create($data);

            // Attach categories and tags
            if (!empty($categoryIds)) {
                $post->categories()->attach($categoryIds);
            }

            if (!empty($tagIds)) {
                $post->tags()->attach($tagIds);
            }

            // Fire action hook
            Hook::doAction('post.saved', $post);

            return $post->load(['user', 'categories', 'tags']);
        });
    }
}
