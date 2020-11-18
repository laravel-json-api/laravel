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

class ResourceTest extends TestCase
{

    /**
     * @return array
     */
    public function routeProvider(): array
    {
        return [
            'index' => ['GET', '/api/v1/posts', 'index', false],
            'store' => ['POST', '/api/v1/posts', 'store', false],
            'read' => ['GET', '/api/v1/posts/123', 'read', true],
            'update' => ['PATCH', '/api/v1/posts/123', 'update', true],
            'destroy' => ['DELETE', '/api/v1/posts/123', 'destroy', true],
        ];
    }

    /**
     * @param string $method
     * @param string $uri
     * @param string $action
     * @param bool $id
     * @dataProvider routeProvider
     */
    public function test(string $method, string $uri, string $action, bool $id): void
    {
        $server = $this->createServer('v1');
        $this->createSchema($server, 'posts', '\d+');

        $this->defaultApiRoutes(function () {
            JsonApiRoute::server('v1')->prefix('v1')->namespace('Api\\V1')->resources(function ($server) {
                $server->resource('posts');
            });
        });

        $route = $this->assertMatch($method, $uri);
        $this->assertSame("App\Http\Controllers\Api\V1\PostController@{$action}", $route->action['controller']);
        $this->assertSame("v1.posts.{$action}", $route->getName());
        $this->assertSame(['api', 'json-api:v1'], $route->action['middleware']);
        $this->assertSame('posts', $route->parameter('resource_type'));

        if ($id) {
            $this->assertSame('post', $route->parameter('resource_id_name'));
            $this->assertSame('\d+', $route->action['where']['post'] ?? null);
        } else {
            $this->assertFalse($route->hasParameter('resource_id_name'));
            $this->assertArrayNotHasKey('post', $route->action['where']);
        }
    }

    /**
     * @param string $method
     * @param string $uri
     * @param string $action
     * @dataProvider routeProvider
     */
    public function testServerName(string $method, string $uri, string $action): void
    {
        $server = $this->createServer('v1');
        $this->createSchema($server, 'posts', '\d+');

        $this->defaultApiRoutes(function () {
            JsonApiRoute::server('v1')
                ->prefix('v1')
                ->namespace('Api\\V1')
                ->name('foobar:')
                ->resources(function ($server) {
                    $server->resource('posts');
                });
        });

        $route = $this->assertMatch($method, $uri);
        $this->assertSame("foobar:posts.{$action}", $route->getName());
    }

    /**
     * @param string $method
     * @param string $uri
     * @param string $action
     * @dataProvider routeProvider
     */
    public function testResourceName(string $method, string $uri, string $action): void
    {
        $server = $this->createServer('v1');
        $this->createSchema($server, 'posts', '\d+');

        $this->defaultApiRoutes(function () use ($action) {
            JsonApiRoute::server('v1')
                ->prefix('v1')
                ->namespace('Api\\V1')
                ->resources(function ($server) use ($action) {
                    $server->resource('posts')->name($action, 'my-posts.foobar');
                });
        });

        $route = $this->assertMatch($method, $uri);
        $this->assertSame("v1.my-posts.foobar", $route->getName());
    }

    /**
     * @param string $method
     * @param string $uri
     * @dataProvider routeProvider
     */
    public function testServerMiddleware(string $method, string $uri): void
    {
        $server = $this->createServer('v1');
        $this->createSchema($server, 'posts', '\d+');

        $this->defaultApiRoutes(function () {
            JsonApiRoute::server('v1')
                ->prefix('v1')
                ->namespace('Api\\V1')
                ->middleware('foo')
                ->resources(function ($server) {
                    $server->resource('posts');
                });
        });

        $route = $this->assertMatch($method, $uri);
        $this->assertSame(['api', 'json-api:v1', 'foo'], $route->action['middleware']);
    }

    /**
     * @param string $method
     * @param string $uri
     * @dataProvider routeProvider
     */
    public function testResourceMiddleware(string $method, string $uri): void
    {
        $server = $this->createServer('v1');
        $this->createSchema($server, 'posts', '\d+');

        $this->defaultApiRoutes(function () {
            JsonApiRoute::server('v1')
                ->prefix('v1')
                ->namespace('Api\\V1')
                ->middleware('foo')
                ->resources(function ($server) {
                    $server->resource('posts')->middleware('bar');
                });
        });

        $route = $this->assertMatch($method, $uri);
        $this->assertSame(['api', 'json-api:v1', 'foo', 'bar'], $route->action['middleware']);
    }

    /**
     * @param string $method
     * @param string $uri
     * @dataProvider routeProvider
     */
    public function testServerDomain(string $method, string $uri): void
    {
        $server = $this->createServer('v1');
        $this->createSchema($server, 'posts', '\d+');

        $this->defaultApiRoutes(function () {
            JsonApiRoute::server('v1')
                ->prefix('v1')
                ->namespace('Api\\V1')
                ->domain('http://api.example.com')
                ->resources(function ($server) {
                    $server->resource('posts');
                });
        });

        $route = $this->assertMatch($method, "http://api.example.com{$uri}");
        $this->assertSame('http://api.example.com', $route->action['domain']);
    }

    /**
     * @param string $method
     * @param string $uri
     * @dataProvider routeProvider
     */
    public function testResourceUri(string $method, string $uri): void
    {
        $server = $this->createServer('v1');
        $this->createSchema($server, 'blog:posts');

        $this->defaultApiRoutes(function () {
            JsonApiRoute::server('v1')
                ->prefix('v1')
                ->namespace('Api\\V1')
                ->resources(function ($server) {
                    $server->resource('blog:posts')->uri('posts');
                });
        });

        $this->assertMatch($method, $uri);
    }

    /**
     * @param string $method
     * @param string $uri
     * @param string $action
     * @param bool $id
     * @dataProvider routeProvider
     */
    public function testResourceParameter(string $method, string $uri, string $action, bool $id): void
    {
        $server = $this->createServer('v1');
        $this->createSchema($server, 'posts', '\d+');

        $this->defaultApiRoutes(function () {
            JsonApiRoute::server('v1')
                ->prefix('v1')
                ->namespace('Api\\V1')
                ->resources(function ($server) {
                    $server->resource('posts')->parameter('blog_post');
                });
        });

        $route = $this->assertMatch($method, $uri);

        if ($id) {
            $this->assertSame('blog_post', $route->parameter('resource_id_name'));
            $this->assertArrayHasKey('blog_post', $route->action['where']);
            $this->assertSame('\d+', $route->action['where']['blog_post']);
        }
    }

    /**
     * @return array
     */
    public function onlyProvider(): array
    {
        return [
            ['index', [
                ['GET', '/api/v1/posts', 200],
                ['POST', '/api/v1/posts', 405],
                ['GET', '/api/v1/posts/1', 404],
                ['PATCH', '/api/v1/posts/1', 404],
                ['DELETE', '/api/v1/posts/1', 404],
            ]],
            [['index', 'read'], [
                ['GET', '/api/v1/posts', 200],
                ['POST', '/api/v1/posts', 405],
                ['GET', '/api/v1/posts/1', 200],
                ['PATCH', '/api/v1/posts/1', 405],
                ['DELETE', '/api/v1/posts/1', 405],
            ]],
            [['store', 'read', 'update', 'destroy'], [
                ['GET', '/api/v1/posts', 405],
                ['POST', '/api/v1/posts', 200],
                ['GET', '/api/v1/posts/1', 200],
                ['PATCH', '/api/v1/posts/1', 200],
                ['DELETE', '/api/v1/posts/1', 200],
            ]],
            // the old package used 'create' and 'delete' instead of 'store' and 'destroy'
            [['create', 'read', 'update', 'delete'], [
                ['GET', '/api/v1/posts', 405],
                ['POST', '/api/v1/posts', 200],
                ['GET', '/api/v1/posts/1', 200],
                ['PATCH', '/api/v1/posts/1', 200],
                ['DELETE', '/api/v1/posts/1', 200],
            ]],
        ];
    }

    /**
     * @param string|array $only
     * @param array $matches
     * @dataProvider onlyProvider
     */
    public function testOnly($only, array $matches): void
    {
        $server = $this->createServer('v1');
        $this->createSchema($server, 'posts', '\d+');

        $this->defaultApiRoutes(function () use ($only) {
            JsonApiRoute::server('v1')
                ->prefix('v1')
                ->namespace('Api\\V1')
                ->resources(function ($server) use ($only) {
                    $server->resource('posts')->only(...Arr::wrap($only));
                });
        });

        $this->assertRoutes($matches);
    }

    /**
     * @return array
     */
    public function exceptProvider(): array
    {
        return [
            ['store', [
                ['GET', '/api/v1/posts', 200],
                ['POST', '/api/v1/posts', 405],
                ['GET', '/api/v1/posts/1', 200],
                ['PATCH', '/api/v1/posts/1', 200],
                ['DELETE', '/api/v1/posts/1', 200],
            ]],
            [['update', 'destroy'], [
                ['GET', '/api/v1/posts', 200],
                ['POST', '/api/v1/posts', 200],
                ['GET', '/api/v1/posts/1', 200],
                ['PATCH', '/api/v1/posts/1', 405],
                ['DELETE', '/api/v1/posts/1', 405],
            ]],
            // the old package used 'create' instead of 'store'
            [['index', 'create'], [
                ['GET', '/api/v1/posts', 404],
                ['POST', '/api/v1/posts', 404],
                ['GET', '/api/v1/posts/1', 200],
                ['PATCH', '/api/v1/posts/1', 200],
                ['DELETE', '/api/v1/posts/1', 200],
            ]],
            // the old package used 'delete' instead of 'destroy'
            [['update', 'delete'], [
                ['GET', '/api/v1/posts', 200],
                ['POST', '/api/v1/posts', 200],
                ['GET', '/api/v1/posts/1', 200],
                ['PATCH', '/api/v1/posts/1', 405],
                ['DELETE', '/api/v1/posts/1', 405],
            ]],
        ];
    }

    /**
     * @param string|array $except
     * @param array $matches
     * @dataProvider exceptProvider
     */
    public function testExcept($except, array $matches): void
    {
        $server = $this->createServer('v1');
        $this->createSchema($server, 'posts', '\d+');

        $this->defaultApiRoutes(function () use ($except) {
            JsonApiRoute::server('v1')
                ->prefix('v1')
                ->namespace('Api\\V1')
                ->resources(function ($server) use ($except) {
                    $server->resource('posts')->except(...Arr::wrap($except));
                });
        });

        $this->assertRoutes($matches);
    }

    public function testReadOnly(): void
    {
        $server = $this->createServer('v1');
        $this->createSchema($server, 'posts', '\d+');

        $this->defaultApiRoutes(function () {
            JsonApiRoute::server('v1')
                ->prefix('v1')
                ->namespace('Api\\V1')
                ->resources(function ($server) {
                    $server->resource('posts')->readOnly();
                });
        });

        $this->assertRoutes([
            ['GET', '/api/v1/posts', 200],
            ['POST', '/api/v1/posts', 405],
            ['GET', '/api/v1/posts/1', 200],
            ['PATCH', '/api/v1/posts/1', 405],
            ['DELETE', '/api/v1/posts/1', 405],
        ]);
    }

    /**
     * @return array
     */
    public function multiWordProvider(): array
    {
        return [
            'dash' => ['blog-posts', 'blog_post'],
            'underscore' => ['blog_posts', 'blog_post'],
            'camel' => ['blogPosts', 'blogPost'],
        ];
    }

    /**
     * @param string $type
     * @param string $parameter
     * @dataProvider multiWordProvider
     * @see https://github.com/cloudcreativity/laravel-json-api/issues/224
     */
    public function testMultiWord(string $type, string $parameter): void
    {
        $server = $this->createServer('v1');
        $this->createSchema($server, $type, '\d+');

        $this->defaultApiRoutes(function () use ($type) {
            JsonApiRoute::server('v1')
                ->prefix('v1')
                ->namespace('Api\\V1')
                ->resources(function ($server) use ($type) {
                    $server->resource($type);
                });
        });

        $route = $this->assertMatch('GET', "/api/v1/{$type}/123");
        $this->assertSame("v1.{$type}.read", $route->getName());
        $this->assertSame($type, $route->parameter('resource_type'));
        $this->assertSame($parameter, $route->parameter('resource_id_name'));
        $this->assertSame('\d+', $route->action['where'][$parameter] ?? null);
    }

}
