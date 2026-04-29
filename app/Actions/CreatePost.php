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
    public function execute(array $data, array $categoryIds = [], array $tagIds = [], array $metaFields = []): Post
    {
        return DB::transaction(function () use ($data, $categoryIds, $tagIds, $metaFields) {
            // Apply filters before creating
            $data = Hook::applyFilters('post.create.data', $data);

            // Fire action hook before creation
            Hook::doAction('post.creating', $data);

            // Handle content: Always prefer rendering from content_raw if available
            if (!empty($data['content_raw'])) {
                $contentRaw = $data['content_raw'];
                if (is_array($contentRaw)) {
                    $data['content_html'] = $this->renderer->render($contentRaw);
                }
            } elseif (isset($data['content_html'])) {
                if (trim($data['content_html']) === '' || trim($data['content_html']) === '<p></p>' || trim($data['content_html']) === '<p><br></p>') {
                    $data['content_html'] = null;
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

            // Save meta fields inside transaction
            if (!empty($metaFields) && is_array($metaFields)) {
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
