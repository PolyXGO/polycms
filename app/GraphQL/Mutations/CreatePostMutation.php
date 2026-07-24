<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Actions\CreatePost;
use App\Facades\Hook;
use App\Models\Post;
use App\Services\ContentRenderer;
use Illuminate\Support\Facades\DB;

class CreatePostMutation
{
    public function __construct(
        protected CreatePost $createPostAction,
        protected ContentRenderer $renderer
    ) {}

    /**
     * Create a new post
     */
    public function __invoke($_, array $args): Post
    {
        $input = $args['input'];
        $categoryIds = [];
        $tagIds = [];

        // Handle categories sync
        if (isset($input['categories'])) {
            if (isset($input['categories']['sync'])) {
                $categoryIds = $input['categories']['sync'];
            } elseif (isset($input['categories']['connect'])) {
                $categoryIds = $input['categories']['connect'];
            }
            unset($input['categories']);
        }

        // Handle tags sync
        if (isset($input['tags'])) {
            if (isset($input['tags']['sync'])) {
                $tagIds = $input['tags']['sync'];
            } elseif (isset($input['tags']['connect'])) {
                $tagIds = $input['tags']['connect'];
            }
            unset($input['tags']);
        }

        // Set user_id from authenticated user
        $input['user_id'] = auth()->id();

        // Render content if content_raw is provided
        if (isset($input['content_raw']) && is_array($input['content_raw'])) {
            $input['content_raw'] = Hook::applyFilters('post.content_raw.before_render', $input['content_raw']);
            $input['content_html'] = $this->renderer->render($input['content_raw']);
        }

        return $this->createPostAction->execute($input, $categoryIds, $tagIds);
    }
}
