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

            // Render content from blocks if provided
            if (isset($data['content_raw']) && is_array($data['content_raw'])) {
                $data['content_html'] = $this->renderer->render($data['content_raw']);
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
