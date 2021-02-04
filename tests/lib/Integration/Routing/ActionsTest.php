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

namespace LaravelJsonApi\Laravel\Tests\Integration\Routing;

use LaravelJsonApi\Laravel\Facades\JsonApiRoute;

class ActionsTest extends TestCase
{

    /**
     * @return array
     */
    public function methodProvider(): array
    {
        return [
            'GET' => ['GET'],
            'POST' => ['POST'],
            'PATCH' => ['PATCH'],
            'PUT' => ['PUT'],
            'DELETE' => ['DELETE'],
            'OPTIONS' => ['OPTIONS'],
        ];
    }

    /**
     * @param string $method
     * @dataProvider methodProvider
     */
    public function testBase(string $method): void
    {
        $func = strtolower($method);
        $server = $this->createServer('v1');
        $this->createSchema($server, 'posts', '\d+');

        $this->defaultApiRoutesWithNamespace(function () use ($func) {
            JsonApiRoute::server('v1')
                ->prefix('v1')
                ->namespace('Api\\V1')
                ->resources(function ($server) use ($func) {
                    $server->resource('posts')->actions(function ($actions) use ($func) {
                        $actions->{$func}('foo-bar')->name('foobar');
                    });
                });
        });

        $route = $this->assertMatch($method, '/api/v1/posts/foo-bar');
        $this->assertSame("App\Http\Controllers\Api\V1\PostController@fooBar", $route->action['controller']);
        $this->assertSame("v1.posts.foobar", $route->getName());
        $this->assertSame(['api', 'jsonapi:v1'], $route->action['middleware']);
        $this->assertSame('posts', $route->parameter('resource_type'));
        $this->assertNull($route->parameter('resource_id_name'));
        $this->assertArrayNotHasKey('post', $route->wheres);
    }

    /**
     * @param string $method
     * @dataProvider methodProvider
     */
    public function testBaseWithPrefix(string $method): void
    {
        $func = strtolower($method);
        $server = $this->createServer('v1');
        $this->createSchema($server, 'posts', '\d+');

        $this->defaultApiRoutesWithNamespace(function () use ($func) {
            JsonApiRoute::server('v1')
                ->prefix('v1')
                ->namespace('Api\\V1')
                ->resources(function ($server) use ($func) {
                    $server->resource('posts')->actions('-actions', function ($actions) use ($func) {
                        $actions->{$func}('foo-bar')->name('foobar');
                    });
                });
        });

        $route = $this->assertMatch($method, '/api/v1/posts/-actions/foo-bar');
        $this->assertSame("App\Http\Controllers\Api\V1\PostController@fooBar", $route->action['controller']);
        $this->assertSame("v1.posts.foobar", $route->getName());
        $this->assertSame(['api', 'jsonapi:v1'], $route->action['middleware']);
        $this->assertSame('posts', $route->parameter('resource_type'));
        $this->assertNull($route->parameter('resource_id_name'));
        $this->assertArrayNotHasKey('post', $route->wheres);
    }

    /**
     * @param string $method
     * @dataProvider methodProvider
     */
    public function testId(string $method): void
    {
        $func = strtolower($method);
        $server = $this->createServer('v1');
        $this->createSchema($server, 'posts', '\d+');

        $this->defaultApiRoutesWithNamespace(function () use ($func) {
            JsonApiRoute::server('v1')
                ->prefix('v1')
                ->namespace('Api\\V1')
                ->resources(function ($server) use ($func) {
                    $server->resource('posts')->actions(function ($actions) use ($func) {
                        $actions->withId()->{$func}('foo-bar')->name('foobar');
                    });
                });
        });

        $route = $this->assertMatch($method, '/api/v1/posts/123/foo-bar');
        $this->assertSame("App\Http\Controllers\Api\V1\PostController@fooBar", $route->action['controller']);
        $this->assertSame("v1.posts.foobar", $route->getName());
        $this->assertSame(['api', 'jsonapi:v1'], $route->action['middleware']);
        $this->assertSame('posts', $route->parameter('resource_type'));
        $this->assertSame('post', $route->parameter('resource_id_name'));
        $this->assertSame('\d+', $route->wheres['post'] ?? null);
    }

    /**
     * @param string $method
     * @dataProvider methodProvider
     */
    public function testIdWithPrefix(string $method): void
    {
        $func = strtolower($method);
        $server = $this->createServer('v1');
        $this->createSchema($server, 'posts', '\d+');

        $this->defaultApiRoutesWithNamespace(function () use ($func) {
            JsonApiRoute::server('v1')
                ->prefix('v1')
                ->namespace('Api\\V1')
                ->resources(function ($server) use ($func) {
                    $server->resource('posts')->actions('-actions', function ($actions) use ($func) {
                        $actions->withId()->{$func}('foo-bar')->name('foobar');
                    });
                });
        });

        $route = $this->assertMatch($method, '/api/v1/posts/123/-actions/foo-bar');
        $this->assertSame("App\Http\Controllers\Api\V1\PostController@fooBar", $route->action['controller']);
        $this->assertSame("v1.posts.foobar", $route->getName());
        $this->assertSame(['api', 'jsonapi:v1'], $route->action['middleware']);
        $this->assertSame('posts', $route->parameter('resource_type'));
        $this->assertSame('post', $route->parameter('resource_id_name'));
        $this->assertSame('\d+', $route->wheres['post'] ?? null);
    }

    /**
     * @param string $method
     * @dataProvider methodProvider
     */
    public function testIdConstraintWorks(string $method): void
    {
        $func = strtolower($method);
        $server = $this->createServer('v1');
        $this->createSchema($server, 'posts', '\d+');

        $this->defaultApiRoutesWithNamespace(function () use ($func) {
            JsonApiRoute::server('v1')
                ->prefix('v1')
                ->namespace('Api\\V1')
                ->resources(function ($server) use ($func) {
                    $server->resource('posts')->actions('-actions', function ($actions) use ($func) {
                        $actions->withId()->{$func}('foo-bar')->name('foobar');
                    });
                });
        });

        $this->assertNotFound($method, '/api/v1/posts/123abc/-actions/foo-bar');
    }
}
