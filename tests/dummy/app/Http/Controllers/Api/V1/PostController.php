<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
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
