<?php
/**
 * Copyright 2020 Cloud Creativity Limited
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

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
