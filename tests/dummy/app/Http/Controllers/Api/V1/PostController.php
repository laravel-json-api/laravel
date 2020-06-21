<?php

declare(strict_types=1);

namespace DummyApp\Http\Controllers\Api\V1;

use DummyApp\JsonApi\V1\Posts\PostResource;
use DummyApp\Post;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use LaravelJsonApi\Core\Resources\ResourceCollection;

class PostController
{

    /**
     * @param Request $request
     * @return Responsable
     */
    public function index(Request $request): Responsable
    {
        if ($request->query->has('page')) {
            $posts = Post::query()->paginate($request->query('page')['size'] ?? null);
        } else {
            $posts = Post::all();
        }

        return new ResourceCollection($posts);
    }

    /**
     * @param Post $post
     * @return Responsable
     */
    public function read(Post $post): Responsable
    {
        return new PostResource($post);
    }
}
