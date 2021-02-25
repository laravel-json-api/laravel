<?php
/*
 * Copyright 2021 Cloud Creativity Limited
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

namespace LaravelJsonApi\Laravel\Tests\Integration;

use App\JsonApi\V1\Server;
use App\Models\Post;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Route;
use LaravelJsonApi\Core\Responses\DataResponse;
use LaravelJsonApi\Core\Responses\ErrorResponse;
use LaravelJsonApi\Core\Responses\MetaResponse;
use LaravelJsonApi\Testing\MakesJsonApiRequests;

class ResponseTest extends TestCase
{

    use DatabaseMigrations;
    use MakesJsonApiRequests;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations();
        $this->loadMigrationsFrom(__DIR__ . '/../../dummy/database/migrations');

        config()->set('jsonapi', [
            'servers' => ['v1' => Server::class],
        ]);
    }

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

        $response->assertFetchedOne($post);
    }

    public function testResources(): void
    {
        $posts = Post::factory()->count(2)->create();

        Route::get('/test', fn() => DataResponse::make($posts)->withServer('v1'));

        $response = $this
            ->withoutExceptionHandling()
            ->jsonApi('posts')
            ->get('/test');

        $response->assertFetchedMany($posts);
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
