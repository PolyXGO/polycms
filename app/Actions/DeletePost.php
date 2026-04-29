<?php

declare(strict_types=1);

namespace App\Actions;

use App\Facades\Hook;
use App\Models\Post;

class DeletePost
{
    /**
     * Delete a post
     */
    public function execute(Post $post): bool
    {
        // Fire action hook before deletion
        Hook::doAction('post.deleting', $post);

        $deleted = $post->delete();

        if ($deleted) {
            // Fire action hook after deletion
            Hook::doAction('post.deleted', $post);
        }

        return $deleted;
    }
}
