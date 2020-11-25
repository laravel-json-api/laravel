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

namespace LaravelJsonApi\Laravel\Tests\Integration\Routing;

use LaravelJsonApi\Core\Support\Arr;
use LaravelJsonApi\Laravel\Facades\JsonApiRoute;

class HasOneTest extends TestCase
{

    /**
     * @return array
     */
    public function genericProvider(): array
    {
        return [
            'showRelated' => [
                'GET',
                '/api/v1/posts/123/author',
                'showRelated',
                'author',
            ],
            'showRelationship' => [
                'GET',
                '/api/v1/posts/123/relationships/author',
                'showRelationship',
                'author.show',
            ],
            'updateRelationship' => [
                'PATCH',
                '/api/v1/posts/123/relationships/author',
                'updateRelationship',
                'author.update',
            ],
        ];
    }

    /**
     * @param string $method
     * @param string $uri
     * @param string $action
     * @param string $name
     * @dataProvider genericProvider
     */
    public function test(string $method, string $uri, string $action, string $name): void
    {
        $server = $this->createServer('v1');
        $this->createSchema($server, 'posts', '\d+');

        $this->defaultApiRoutesWithNamespace(function () {
            JsonApiRoute::server('v1')->prefix('v1')->namespace('Api\\V1')->resources(function ($server) {
                $server->resource('posts')->relationships(function ($relations) {
                    $relations->hasOne('author');
                });
            });
        });

        $route = $this->assertMatch($method, $uri);
        $this->assertSame("App\Http\Controllers\Api\V1\PostController@{$action}", $route->action['controller']);
        $this->assertSame("v1.posts.{$name}", $route->getName());
        $this->assertSame(['api', 'jsonapi:v1'], $route->action['middleware']);
        $this->assertSame('posts', $route->parameter('resource_type'));
        $this->assertSame('post', $route->parameter('resource_id_name'));
        $this->assertSame('\d+', $route->action['where']['post'] ?? null);
        $this->assertSame('author', $route->parameter('resource_relationship'));
    }


    /**
     * @param string $method
     * @param string $uri
     * @param string $action
     * @param string $name
     * @dataProvider genericProvider
     */
    public function testName(string $method, string $uri, string $action, string $name): void
    {
        $server = $this->createServer('v1');
        $this->createSchema($server, 'posts', '\d+');

        $this->defaultApiRoutesWithNamespace(function () use ($action) {
            JsonApiRoute::server('v1')
                ->prefix('v1')
                ->namespace('Api\\V1')
                ->name('my-api:')
                ->resources(function ($server) use ($action) {
                    $server->resource('posts')->relationships(function ($relations) use ($action) {
                        $relations->hasOne('author')->name($action, 'foobar.bazbat');
                    });
                });
        });

        $route = $this->assertMatch($method, $uri);
        $this->assertSame("my-api:posts.foobar.bazbat", $route->getName());
    }

    /**
     * @param string $method
     * @param string $uri
     * @param string $action
     * @param string $name
     * @dataProvider genericProvider
     */
    public function testMiddleware(string $method, string $uri, string $action, string $name): void
    {
        $server = $this->createServer('v1');
        $this->createSchema($server, 'posts', '\d+');

        $this->defaultApiRoutesWithNamespace(function () {
            JsonApiRoute::server('v1')
                ->prefix('v1')
                ->namespace('Api\\V1')
                ->middleware('foo')
                ->resources(function ($server) {
                    $server->resource('posts')->middleware('bar')->relationships(function ($relations) {
                        $relations->hasOne('author')->middleware('baz');
                    });
                });
        });

        $route = $this->assertMatch($method, $uri);
        $this->assertSame(['api', 'jsonapi:v1', 'foo', 'bar', 'baz'], $route->action['middleware']);
    }

    /**
     * @param string $method
     * @param string $uri
     * @param string $action
     * @param string $name
     * @dataProvider genericProvider
     */
    public function testUri(string $method, string $uri, string $action, string $name): void
    {
        $server = $this->createServer('v1');
        $this->createSchema($server, 'posts', '\d+');

        $this->defaultApiRoutesWithNamespace(function () {
            JsonApiRoute::server('v1')
                ->prefix('v1')
                ->namespace('Api\\V1')
                ->resources(function ($server) {
                    $server->resource('posts')->relationships(function ($relations) {
                        $relations->hasOne('user')->uri('author');
                    });
                });
        });

        $this->assertMatch($method, $uri);
    }

    /**
     * @return array[]
     */
    public function onlyProvider(): array
    {
        return [
            ['related', [
                ['GET', '/api/v1/posts/1/author', 200],
                ['GET', '/api/v1/posts/1/relationships/author', 404],
                ['PATCH', '/api/v1/posts/1/relationships/author', 404],
            ]],
            [['related', 'show'], [
                ['GET', '/api/v1/posts/1/author', 200],
                ['GET', '/api/v1/posts/1/relationships/author', 200],
                ['PATCH', '/api/v1/posts/1/relationships/author', 405],
            ]],
            ['update', [
                ['GET', '/api/v1/posts/1/author', 404],
                ['GET', '/api/v1/posts/1/relationships/author', 405],
                ['PATCH', '/api/v1/posts/1/relationships/author', 200],
            ]],
            // the old package used 'replace' instead of 'update'
            ['replace', [
                ['GET', '/api/v1/posts/1/author', 404],
                ['GET', '/api/v1/posts/1/relationships/author', 405],
                ['PATCH', '/api/v1/posts/1/relationships/author', 200],
            ]],
        ];
    }

    /**
     * @param $only
     * @param array $matches
     * @dataProvider onlyProvider
     */
    public function testOnly($only, array $matches): void
    {
        $server = $this->createServer('v1');
        $this->createSchema($server, 'posts', '\d+');

        $this->defaultApiRoutesWithNamespace(function () use ($only) {
            JsonApiRoute::server('v1')
                ->prefix('v1')
                ->namespace('Api\\V1')
                ->resources(function ($server) use ($only) {
                    $server->resource('posts')->relationships(function ($relations) use ($only) {
                        $relations->hasOne('author')->only(...Arr::wrap($only));
                    });
                });
        });

        $this->assertRoutes($matches);
    }

    /**
     * @return array
     */
    public function exceptProvider()
    {
        return [
            ['related', [
                ['GET', '/api/v1/posts/1/author', 404],
                ['GET', '/api/v1/posts/1/relationships/author', 200],
                ['PATCH', '/api/v1/posts/1/relationships/author', 200],
            ]],
            [['related', 'show'], [
                ['GET', '/api/v1/posts/1/author', 404],
                ['GET', '/api/v1/posts/1/relationships/author', 405],
                ['PATCH', '/api/v1/posts/1/relationships/author', 200],
            ]],
            ['update', [
                ['GET', '/api/v1/posts/1/author', 200],
                ['GET', '/api/v1/posts/1/relationships/author', 200],
                ['PATCH', '/api/v1/posts/1/relationships/author', 405],
            ]],
            // the old package used 'replace' instead of 'update'
            ['replace', [
                ['GET', '/api/v1/posts/1/author', 200],
                ['GET', '/api/v1/posts/1/relationships/author', 200],
                ['PATCH', '/api/v1/posts/1/relationships/author', 405],
            ]],
        ];
    }


    /**
     * @param $except
     * @param array $matches
     * @dataProvider exceptProvider
     */
    public function testExcept($except, array $matches): void
    {
        $server = $this->createServer('v1');
        $this->createSchema($server, 'posts', '\d+');

        $this->defaultApiRoutesWithNamespace(function () use ($except) {
            JsonApiRoute::server('v1')
                ->prefix('v1')
                ->namespace('Api\\V1')
                ->resources(function ($server) use ($except) {
                    $server->resource('posts')->relationships(function ($relations) use ($except) {
                        $relations->hasOne('author')->except(...Arr::wrap($except));
                    });
                });
        });

        $this->assertRoutes($matches);
    }

    public function testReadOnly(): void
    {
        $server = $this->createServer('v1');
        $this->createSchema($server, 'posts', '\d+');

        $this->defaultApiRoutesWithNamespace(function () {
            JsonApiRoute::server('v1')
                ->prefix('v1')
                ->namespace('Api\\V1')
                ->resources(function ($server) {
                    $server->resource('posts')->relationships(function ($relations) {
                        $relations->hasOne('author')->readOnly();
                    });
                });
        });

        $this->assertRoutes([
            ['GET', '/api/v1/posts/1/author', 200],
            ['GET', '/api/v1/posts/1/relationships/author', 200],
            ['PATCH', '/api/v1/posts/1/relationships/author', 405],
        ]);
    }

    /**
     * @return array
     */
    public function ownActionProvider(): array
    {
        return [
            'showRelated' => [
                'GET',
                '/api/v1/posts/123/author',
                'related',
                'showRelatedAuthor',
            ],
            'showRelationship' => [
                'GET',
                '/api/v1/posts/123/relationships/author',
                'show',
                'showAuthor',
            ],
            'updateRelationship' => [
                'PATCH',
                '/api/v1/posts/123/relationships/author',
                'update',
                'updateAuthor',
            ],
        ];
    }

    /**
     * @param string $method
     * @param string $uri
     * @param string $action
     * @param string $expected
     * @dataProvider ownActionProvider
     */
    public function testOwnAction(string $method, string $uri, string $action, string $expected): void
    {
        $server = $this->createServer('v1');
        $this->createSchema($server, 'posts', '\d+');

        $this->defaultApiRoutesWithNamespace(function () use ($action) {
            JsonApiRoute::server('v1')->prefix('v1')->namespace('Api\\V1')->resources(function ($server) use ($action) {
                $server->resource('posts')->relationships(function ($relations) use ($action) {
                    $relations->hasOne('author')->ownAction($action);
                });
            });
        });

        $route = $this->assertMatch($method, $uri);
        $this->assertSame("App\Http\Controllers\Api\V1\PostController@{$expected}", $route->action['controller']);
        $this->assertSame('posts', $route->parameter('resource_type'));
        $this->assertSame('post', $route->parameter('resource_id_name'));
        $this->assertSame('author', $route->parameter('resource_relationship'));
    }

    /**
     * @param string $method
     * @param string $uri
     * @param string $action
     * @param string $expected
     * @dataProvider ownActionProvider
     */
    public function testOwnActions(string $method, string $uri, string $action, string $expected): void
    {
        $server = $this->createServer('v1');
        $this->createSchema($server, 'posts', '\d+');

        $this->defaultApiRoutesWithNamespace(function () {
            JsonApiRoute::server('v1')->prefix('v1')->namespace('Api\\V1')->resources(function ($server) {
                $server->resource('posts')->relationships(function ($relations) {
                    $relations->hasOne('author')->ownActions();
                });
            });
        });

        $route = $this->assertMatch($method, $uri);
        $this->assertSame("App\Http\Controllers\Api\V1\PostController@{$expected}", $route->action['controller']);
        $this->assertSame('posts', $route->parameter('resource_type'));
        $this->assertSame('post', $route->parameter('resource_id_name'));
        $this->assertSame('author', $route->parameter('resource_relationship'));
    }
}
