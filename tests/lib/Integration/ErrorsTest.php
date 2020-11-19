<?php
/*
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

namespace LaravelJsonApi\Laravel\Tests\Integration;

use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\Exceptions\MaintenanceModeException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\MessageBag;
use Illuminate\Validation\ValidationException;
use LaravelJsonApi\Core\Document\Error;
use LaravelJsonApi\Core\Exceptions\JsonApiException;
use LaravelJsonApi\Laravel\Facades\JsonApiRoute;
use LaravelJsonApi\Testing\MakesJsonApiRequests;
use LaravelJsonApi\Testing\TestResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ErrorsTest extends TestCase
{

    use MakesJsonApiRequests;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadLaravelMigrations();
        $this->loadMigrationsFrom(__DIR__ . '/../../dummy/database/migrations');

        config()->set('json-api', [
            'servers' => [
                'v1' => \App\JsonApi\V1\Server::class,
            ],
        ]);

        $this->defaultApiRoutes(function () {
            JsonApiRoute::server('v1')->prefix('v1')->namespace('Api\V1')->resources(function ($server) {
                $server->resource('posts')->relationships(function ($relations) {
                    $relations->hasMany('tags');
                });
            });
        });
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function resolveApplicationExceptionHandler($app)
    {
        $app->singleton(
            \Illuminate\Contracts\Debug\ExceptionHandler::class,
            \LaravelJsonApi\Testing\TestExceptionHandler::class
        );
    }

    public function testNotFound(): void
    {
        $response = $this
            ->jsonApi()
            ->get('/api/v1/posts/9999');

        $response->assertExactErrorStatus([
            'status' => '404',
            'title' => 'Not Found',
        ]);
    }

    public function testInvalidJson(): void
    {
        $json = <<<JSON
{
    "data": {
        "type": "posts"
    }
JSON;

        $response = $this->sendInvalid('POST', '/api/v1/posts', $json);

        $response->assertExactErrorStatus([
            'code' => '4',
            'detail' => 'Syntax error',
            'status' => '400',
            'title' => 'Invalid JSON',
        ]);
    }

    /**
     * Valid JSON, but it is not an object.
     */
    public function testUnexpectedDocument(): void
    {
        $response = $this->sendInvalid('POST', '/api/v1/posts', '[]');

        $response->assertExactErrorStatus([
            'detail' => 'Expecting JSON to decode to an object.',
            'status' => '400',
            'title' => 'Invalid JSON',
        ]);
    }

    public function testSpecificationError(): void
    {
        $json = <<<JSON
{
    "data": {
        "type": "posts",
        "attributes": {
            "author": null
        },
        "relationships": {
            "title": {
                "data": null
            }
        }
    }
}
JSON;

        $response = $this->sendInvalid('POST', '/api/v1/posts', $json);

        $response->assertExactErrors(400, [
            ['detail' => 'The field author is not a supported attribute.',
                'source' => ['pointer' => '/data/attributes'],
                'status' => '400',
                'title' => 'Non-Compliant JSON API Document',
            ],
            [
                'detail' => 'The field title is not a supported relationship.',
                'source' => ['pointer' => '/data/relationships'],
                'status' => '400',
                'title' => 'Non-Compliant JSON API Document',
            ],
        ]);
    }

    public function testSpecificationErrorOnRelationship(): void
    {
        $post = Post::factory()->create();
        $uri = url('/api/v1/posts', [$post, 'relationships', 'tags']);

        $json = <<<JSON
{
    "data": {
        "type": "tags",
        "id": "123"
    }
}
JSON;

        $response = $this->sendInvalid('POST', $uri, $json);

        $response->assertExactErrorStatus([
            'detail' => 'The member data must be an array.',
            'source' => ['pointer' => '/data'],
            'status' => '400',
            'title' => 'Non-Compliant JSON API Document',
        ]);
    }

    /**
     * A JSON API exception thrown from a non-standard route renders as
     * JSON API.
     */
    public function testJsonApiException(): void
    {
        Route::get('/test', function () {
            throw JsonApiException::error([
                'status' => '418',
                'detail' => "Hello, I'm a teapot.",
            ])->withHeaders(['X-Foo' => 'Bar']);
        });

        $expected = [
            'errors' => [
                [
                    'status' => '418',
                    'detail' => "Hello, I'm a teapot.",
                ],
            ],
            'jsonapi' => [
                'version' => '1.0',
            ],
        ];

        $this->get('/test')
            ->assertStatus(418)
            ->assertHeader('Content-Type', 'application/vnd.api+json')
            ->assertHeader('X-Foo', 'Bar')
            ->assertExactJson($expected);
    }

    public function testMaintenanceMode(): void
    {
        Route::get('/test', function () {
            throw new MaintenanceModeException(Carbon::now()->getTimestamp(), 60, "We'll be back soon.");
        });

        $expected = [
            'errors' => [
                [
                    'title' => 'Service Unavailable',
                    'detail' => "We'll be back soon.",
                    'status' => '503',
                ],
            ],
            'jsonapi' => [
                'version' => '1.0',
            ],
        ];

        $this->get('/test', ['Accept' => 'application/vnd.api+json'])
            ->assertStatus(503)
            ->assertHeader('Content-Type', 'application/vnd.api+json')
            ->assertExactJson($expected);
    }


    /**
     * By default Laravel sends a 419 response for a TokenMismatchException.
     *
     * @see https://github.com/cloudcreativity/laravel-json-api/issues/181
     */
    public function testTokenMismatch(): void
    {
        Route::get('/test', function () {
            throw new TokenMismatchException("The token is not valid.");
        });

        $expected = [
            'errors' => [
                [
                    'detail' => 'The token is not valid.',
                    'status' => '419',
                ],
            ],
            'jsonapi' => [
                'version' => '1.0',
            ],
        ];

        $this->get('/test', ['Accept' => 'application/vnd.api+json'])
            ->assertStatus(419)
            ->assertHeader('Content-Type', 'application/vnd.api+json')
            ->assertExactJson($expected);
    }

    public function testHttpException(): void
    {
        Route::get('/test', function () {
            throw new HttpException(
                418,
                "I think I might be a teapot.",
                null,
                ['X-Teapot' => 'True']
            );
        });

        $expected = [
            'errors' => [
                [
                    'title' => "I'm a teapot",
                    'detail' => 'I think I might be a teapot.',
                    'status' => '418',
                ]
            ],
            'jsonapi' => [
                'version' => '1.0',
            ],
        ];

        $this->get('/test', ['Accept' => 'application/vnd.api+json'])
            ->assertStatus(418)
            ->assertHeader('X-Teapot', 'True')
            ->assertHeader('Content-Type', 'application/vnd.api+json')
            ->assertExactJson($expected);
    }


    /**
     * If we get a Laravel validation exception we need to convert this to
     * JSON API errors.
     */
    public function testValidationException(): void
    {
        $messages = new MessageBag([
            'email' => 'These credentials do not match our records.',
            'foo.bar' => 'Foo bar is not baz.',
        ]);

        $validator = $this->createMock(Validator::class);
        $validator->method('errors')->willReturn($messages);

        Route::get('/test', function () use ($validator) {
            throw new ValidationException($validator);
        });

        $expected = [
            'errors' => [
                [
                    'detail' => 'These credentials do not match our records.',
                    'source' => ['pointer' => '/email'],
                    'status' => '422',
                    'title' => 'Unprocessable Entity',
                ],
                [
                    'detail' => 'Foo bar is not baz.',
                    'source' => ['pointer' => '/foo/bar'],
                    'status' => '422',
                    'title' => 'Unprocessable Entity',
                ],
            ],
            'jsonapi' => [
                'version' => '1.0',
            ],
        ];

        $this->get('/test', ['Accept' => 'application/vnd.api+json'])
            ->assertStatus(422)
            ->assertHeader('Content-Type', 'application/vnd.api+json')
            ->assertExactJson($expected);
    }

    public function testGenericException(): void
    {
        Route::get('/test', function () {
            throw new \Exception('Boom.');
        });

        $expected = [
            'errors' => [
                [
                    'title' => 'Internal Server Error',
                    'status' => '500',
                ],
            ],
            'jsonapi' => [
                'version' => '1.0',
            ],
        ];

        $this->get('/test', ['Accept' => 'application/vnd.api+json'])
            ->assertStatus(500)
            ->assertHeader('Content-Type', 'application/vnd.api+json')
            ->assertExactJson($expected);
    }

    /**
     * @param string $method
     * @param string $uri
     * @param string $content
     * @return TestResponse
     */
    private function sendInvalid(string $method, string $uri, string $content): TestResponse
    {
        $headers = $this->transformHeadersToServerVars([
            'Accept' => 'application/vnd.api+json',
            'CONTENT_TYPE' => 'application/vnd.api+json',
        ]);

        $response = $this->call($method, $uri, [], [], [], $headers, $content);

        return TestResponse::cast($response);
    }
}
