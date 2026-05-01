<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Actions\UpdatePost;
use App\Facades\Hook;
use App\Models\Post;
use App\Services\ContentRenderer;

class UpdatePostMutation
{
    public function __construct(
        protected UpdatePost $updatePostAction,
        protected ContentRenderer $renderer
    ) {}

    /**
     * Update an existing post
     */
    public function __invoke($_, array $args): Post
    {
        $post = Post::findOrFail($args['id']);
        $input = $args['input'];
        $categoryIds = null;
        $tagIds = null;

        // Handle categories sync
        if (isset($input['categories'])) {
            if (isset($input['categories']['sync'])) {
                $categoryIds = $input['categories']['sync'];
            } elseif (isset($input['categories']['connect'])) {
                $categoryIds = array_merge($post->categories->pluck('id')->toArray(), $input['categories']['connect']);
            } elseif (isset($input['categories']['disconnect'])) {
                $categoryIds = array_diff($post->categories->pluck('id')->toArray(), $input['categories']['disconnect']);
            }
            unset($input['categories']);
        }

        // Handle tags sync
        if (isset($input['tags'])) {
            if (isset($input['tags']['sync'])) {
                $tagIds = $input['tags']['sync'];
            } elseif (isset($input['tags']['connect'])) {
                $tagIds = array_merge($post->tags->pluck('id')->toArray(), $input['tags']['connect']);
            } elseif (isset($input['tags']['disconnect'])) {
                $tagIds = array_diff($post->tags->pluck('id')->toArray(), $input['tags']['disconnect']);
            }
            unset($input['tags']);
        }

        // Render content if content_raw is provided
        if (isset($input['content_raw']) && is_array($input['content_raw'])) {
            $input['content_raw'] = Hook::applyFilters('post.content_raw.before_render', $input['content_raw']);
            $input['content_html'] = $this->renderer->render($input['content_raw']);
        }

        return $this->updatePostAction->execute($post, $input, $categoryIds, $tagIds);
    }
}
