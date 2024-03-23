<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace LaravelJsonApi\Laravel\Tests\Acceptance;

use App\Models\Post;
use Illuminate\Support\Facades\Route;
use LaravelJsonApi\Core\Responses\DataResponse;
use LaravelJsonApi\Core\Responses\ErrorResponse;
use LaravelJsonApi\Core\Responses\MetaResponse;

class ResponseTest extends TestCase
{

    /**
     * Test that a data response can be returned from a custom route.
     */
    public function testResource(): void
    {
        $post = Post::factory()->create();

        Route::get('/test', fn() => DataResponse::make($post)->withServer('v1')->didntCreate());

        $response = $this
            ->withoutExceptionHandling()
            ->jsonApi('posts')
            ->get('/test');

        $response->assertFetchedOne([
            'type' => 'posts',
            'id' => $post->getRouteKey(),
        ]);
    }

    public function testResources(): void
    {
        $posts = Post::factory()->count(2)->create();

        $expected = $posts->toBase()->map(fn(Post $post) => [
            'type' => 'posts',
            'id' => $post->getRouteKey(),
        ])->all();

        Route::get('/test', fn() => DataResponse::make($posts)->withServer('v1'));

        $response = $this
            ->withoutExceptionHandling()
            ->jsonApi('posts')
            ->get('/test');

        $response->assertFetchedMany($expected);
    }

    public function testMeta(): void
    {
        Route::get('/test', fn() => MetaResponse::make(['foo' => 'bar']));

        $response = $this
            ->withoutExceptionHandling()
            ->jsonApi()
            ->get('/test');

        $response->assertMetaWithoutData(['foo' => 'bar']);
    }

    public function testErrors(): void
    {
        $error = [
            'status' => '409',
            'title' => 'Conflict',
        ];

        Route::get('/test', fn() => ErrorResponse::error($error));

        $response = $this
            ->withoutExceptionHandling()
            ->jsonApi()
            ->get('/test');

        $response->assertExactErrorStatus($error);
    }
}
