<?php

namespace App\Actions\Blog;

use App\Enums\PostStatus;
use App\Models\Post;

class PublishPostAction
{
    /**
     * Execute the action to publish a post.
     */
    public function execute(Post $post): void
    {
        $post->update([
            'status' => PostStatus::PUBLISHED,
            'published_at' => now(),
        ]);
    }
}
