<?php

declare(strict_types=1);

namespace DummyApp\Http\Controllers\Api\V1;

use DummyApp\JsonApi\V1\Posts\PostResource;
use DummyApp\Post;
use Illuminate\Contracts\Support\Responsable;

class PostController
{

    /**
     * @param Post $post
     * @return Responsable
     */
    public function read(Post $post): Responsable
    {
        return new PostResource($post);
    }
}
