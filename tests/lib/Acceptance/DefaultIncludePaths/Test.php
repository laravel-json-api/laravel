<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace LaravelJsonApi\Laravel\Tests\Acceptance\DefaultIncludePaths;

use App\JsonApi\V1\Posts\PostCollectionQuery;
use App\Models\Post;
use App\Models\Tag;
use LaravelJsonApi\Laravel\Facades\JsonApiRoute;
use LaravelJsonApi\Laravel\Http\Controllers\JsonApiController;
use LaravelJsonApi\Laravel\Tests\Acceptance\TestCase;

class Test extends TestCase
{

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->app->bind(PostCollectionQuery::class, TestRequest::class);

        JsonApiRoute::server('v1')->prefix('api/v1')->resources(function ($server) {
            $server->resource('posts', JsonApiController::class);
        });
    }

    public function test(): void
    {
        $posts = Post::factory()->count(2)->create();
        $tag = Tag::factory()->create();
        $tag->posts()->save($posts[0]);

        $response = $this
            ->withoutExceptionHandling()
            ->jsonApi('posts')
            ->get('/api/v1/posts');

        $response->assertFetchedMany($posts)->assertIncluded([
            ['type' => 'users', 'id' => $posts[0]->author],
            ['type' => 'users', 'id' => $posts[1]->author],
            ['type' => 'tags', 'id' => $tag],
        ]);
    }

    /**
     * When a query request is using default include paths, we expect the client to be able
     * to ask for nothing to be included by providing an empty include path.
     */
    public function testNoneIncluded(): void
    {
        $posts = Post::factory()->count(2)->create();

        $response = $this
            ->withoutExceptionHandling()
            ->jsonApi('posts')
            ->get('/api/v1/posts?include=');

        $response->assertFetchedMany($posts);
        // @TODO assertNoneIncluded should be available on the response.
        $response->getDocument()->assertNoneIncluded();
    }
}
