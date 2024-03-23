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
            ->withHeader('Accept', 'application/vnd.api+json')
            ->delete('/api/v1/posts/' . $post->getRouteKey());

        $response->assertNoContent();
    }

}
