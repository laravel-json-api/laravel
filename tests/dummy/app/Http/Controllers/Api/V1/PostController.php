<?php
/*
 * Copyright 2023 Cloud Creativity Limited
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

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\JsonApi\V1\Posts\PostQuery;
use App\JsonApi\V1\Posts\PostSchema;
use App\Models\Post;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Response;
use LaravelJsonApi\Core\Responses\DataResponse;
use LaravelJsonApi\Laravel\Http\Controllers\Actions;

class PostController extends Controller
{

    use Actions\FetchMany;
    use Actions\FetchOne;
    use Actions\Store;
    use Actions\Update;
    use Actions\Destroy;
    use Actions\FetchRelated;
    use Actions\FetchRelationship;
    use Actions\UpdateRelationship;
    use Actions\AttachRelationship;
    use Actions\DetachRelationship;

    /**
     * @return Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function purge(): Response
    {
        $this->authorize('deleteAll', Post::class);

        Post::query()->forceDelete();

        return response('', 204);
    }

    /**
     * Publish a post.
     *
     * @param PostSchema $schema
     * @param PostQuery $query
     * @param Post $post
     * @return Responsable
     */
    public function publish(PostSchema $schema, PostQuery $query, Post $post): Responsable
    {
        $this->authorize('update', $post);

        abort_if($post->published_at, 403, 'Post is already published.');

        $post->update(['published_at' => now()]);

        $model = $schema
            ->repository()
            ->queryOne($post)
            ->withRequest($query)
            ->first();

        return new DataResponse($model);
    }
}
