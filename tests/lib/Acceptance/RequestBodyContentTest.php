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

namespace LaravelJsonApi\Laravel\Tests\Acceptance;

use App\Models\Post;
use LaravelJsonApi\Laravel\Facades\JsonApiRoute;
use LaravelJsonApi\Laravel\Http\Controllers\JsonApiController;

class RequestBodyContentTest extends TestCase
{

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        JsonApiRoute::server('v1')->prefix('api/v1')->resources(function ($server) {
            $server->resource('posts', JsonApiController::class);
        });
    }

    public function testPostWithoutBody(): void
    {
        $headers = $this->transformHeadersToServerVars([
            'Accept' => 'application/vnd.api+json',
            'Content-Type' => 'application/vnd.api+json',
        ]);

        $response = $this->call('POST', '/api/v1/posts', [], [], [], $headers);

        $response->assertStatus(400)->assertExactJson([
            'jsonapi' => [
                'version' => '1.0',
            ],
            'errors' => [
                [
                    'detail' => 'Expecting JSON to decode.',
                    'status' => '400',
                    'title' => 'Invalid JSON',
                ],
            ],
        ]);
    }

    public function testPatchWithoutBody(): void
    {
        $post = Post::factory()->create();

        $headers = $this->transformHeadersToServerVars([
            'Accept' => 'application/vnd.api+json',
            'Content-Type' => 'application/vnd.api+json',
        ]);

        $response = $this->call('PATCH', "/api/v1/posts/{$post->getRouteKey()}", [], [], [], $headers);

        $response->assertStatus(400)->assertExactJson([
            'jsonapi' => [
                'version' => '1.0',
            ],
            'errors' => [
                [
                    'detail' => 'Expecting JSON to decode.',
                    'status' => '400',
                    'title' => 'Invalid JSON',
                ],
            ],
        ]);
    }

    /**
     * Have observed browsers sending a "Content-Length" header with an empty string on GET
     * requests. If no content is expected, this should be interpreted as not having
     * any content.
     */
    public function testEmptyContentLengthHeader(): void
    {
        $headers = $this->transformHeadersToServerVars(['Content-Length' => '']);
        $this->call('GET', '/api/v1/posts', [], [], [], $headers)->assertSuccessful();
    }

    /**
     * Sending a delete request without a Content Type header should work, as no
     * body content is expected.
     *
     * @see https://github.com/laravel-json-api/laravel/issues/29
     */
    public function testDeleteWithoutBody(): void
    {
        $post = Post::factory()->create();

        $response = $this
            ->withoutExceptionHandling()
            ->actingAs($post->author)
            ->delete('/api/v1/posts/' . $post->getRouteKey());

        $response->assertNoContent();
    }

}
