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

            // Render content from blocks if provided
            if (isset($data['content_raw']) && is_array($data['content_raw'])) {
                $data['content_html'] = $this->renderer->render($data['content_raw']);
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
