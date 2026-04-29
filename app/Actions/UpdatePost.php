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
    public function execute(Post $post, array $data, ?array $categoryIds = null, ?array $tagIds = null, ?array $metaFields = null): Post
    {
        return DB::transaction(function () use ($post, $data, $categoryIds, $tagIds, $metaFields) {
            // Apply filters before updating
            $data = Hook::applyFilters('post.update.data', $data, $post);

            // Fire action hook before updating
            Hook::doAction('post.updating', $post, $data);

            // Handle content: Always prefer rendering from content_raw if available
            if (!empty($data['content_raw'])) {
                $contentRaw = $data['content_raw'];
                // Handle both formats: { blocks: [...] } or direct TipTap JSON
                if (is_array($contentRaw)) {
                    // ContentRenderer now handles the context
                    $data['content_html'] = $this->renderer
                        ->setContext(['post' => $post])
                        ->render($contentRaw);
                }
            } elseif (isset($data['content_html'])) {
                if (trim($data['content_html']) === '' || trim($data['content_html']) === '<p></p>' || trim($data['content_html']) === '<p><br></p>') {
                    $data['content_html'] = null;
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

            // Save meta fields inside transaction
            if (is_array($metaFields)) {
                foreach ($metaFields as $key => $value) {
                    $post->meta()->updateOrCreate(
                        ['meta_key' => $key],
                        ['meta_value' => $value]
                    );
                }
            }

            // Fire action hook
            Hook::doAction('post.saved', $post);

            return $post->load(['user', 'categories', 'tags', 'meta']);
        });
    }
}
