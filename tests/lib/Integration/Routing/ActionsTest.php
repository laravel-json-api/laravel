<?php
/*
 * Copyright 2023 Cloud Creativity Limited
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

use App\Http\Controllers\Api\V1\PostController;
use Illuminate\Contracts\Routing\Registrar;
use Illuminate\Support\Facades\Route;
use LaravelJsonApi\Laravel\Facades\JsonApiRoute;

class ActionsTest extends TestCase
{

    /**
     * @return array
     */
    public static function methodProvider(): array
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
                    $server->resource('posts')->actions(function ($actions, $routes) use ($func) {
                        $this->assertInstanceOf(Registrar::class, $routes);
                        $actions->{$func}('foo-bar');
                    });
                });
        });

        $route = $this->assertMatch($method, '/api/v1/posts/foo-bar');
        $this->assertSame("App\Http\Controllers\Api\V1\PostController@fooBar", $route->action['controller']);
        $this->assertSame("v1.posts.fooBar", $route->getName());
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
                        $actions->{$func}('foo-bar');
                    });
                });
        });

        $route = $this->assertMatch($method, '/api/v1/posts/-actions/foo-bar');
        $this->assertSame("App\Http\Controllers\Api\V1\PostController@fooBar", $route->action['controller']);
        $this->assertSame("v1.posts.fooBar", $route->getName());
        $this->assertSame(['api', 'jsonapi:v1'], $route->action['middleware']);
        $this->assertSame('posts', $route->parameter('resource_type'));
        $this->assertNull($route->parameter('resource_id_name'));
        $this->assertArrayNotHasKey('post', $route->wheres);
    }

    /**
     * @param string $method
     * @dataProvider methodProvider
     */
    public function testBaseWithName(string $method): void
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
                        $actions->withId()->{$func}('foo-bar');
                    });
                });
        });

        $route = $this->assertMatch($method, '/api/v1/posts/123/foo-bar');
        $this->assertSame("App\Http\Controllers\Api\V1\PostController@fooBar", $route->action['controller']);
        $this->assertSame("v1.posts.fooBar", $route->getName());
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
                        $actions->withId()->{$func}('foo-bar');
                    });
                });
        });

        $route = $this->assertMatch($method, '/api/v1/posts/123/-actions/foo-bar');
        $this->assertSame("App\Http\Controllers\Api\V1\PostController@fooBar", $route->action['controller']);
        $this->assertSame("v1.posts.fooBar", $route->getName());
        $this->assertSame(['api', 'jsonapi:v1'], $route->action['middleware']);
        $this->assertSame('posts', $route->parameter('resource_type'));
        $this->assertSame('post', $route->parameter('resource_id_name'));
        $this->assertSame('\d+', $route->wheres['post'] ?? null);
    }

    /**
     * @param string $method
     * @dataProvider methodProvider
     */
    public function testIdWithName(string $method): void
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
                        $actions->withId()->{$func}('foo-bar');
                    });
                });
        });

        $this->assertNotFound($method, '/api/v1/posts/123abc/-actions/foo-bar');
    }

    /**
     * @see https://github.com/laravel-json-api/laravel/issues/90
     */
    public function testWithMiddleware(): void
    {
        $server = $this->createServer('v1');
        $this->createSchema($server, 'posts', '\d+');

        $this->defaultApiRoutes(function () {
            Route::name('api.')->middleware('my-middleware1')->group(function () {
                JsonApiRoute::server('v1')->prefix('v1')->resources(function ($server) {
                    $server->resource('posts', PostController::class)
                        ->actions(function ($actions) {
                            $actions->withId()->get('image');
                        })->middleware('my-middleware2');
                });
            });
        });

        $route = $this->assertMatch('GET', '/api/v1/posts/123/image');
        $this->assertSame("App\Http\Controllers\Api\V1\PostController@image", $route->action['controller']);
        $this->assertSame("api.v1.posts.image", $route->getName());
        $this->assertSame(['api', 'my-middleware1', 'jsonapi:v1', 'my-middleware2'], $route->action['middleware']);
    }

    /**
     * This test was created from a question on Slack. In the test, we check that if an
     * action is registered without a URL prefix, the route matching can distinguish
     * between:
     *
     * DELETE /api/v1/posts/purge
     * DELETE /api/v1/posts/123
     */
    public function testNoActionPrefixCanMatchIfActionDoesNotMatchIdPattern(): void
    {
        $server = $this->createServer('v1');
        $this->createSchema($server, 'posts', '\d+');

        $this->defaultApiRoutes(function () {
            JsonApiRoute::server('v1')->prefix('v1')->resources(function ($server) {
                $server->resource('posts', PostController::class)
                    ->actions(function ($actions) {
                        $actions->delete('purge');
                        $actions->withId()->post('publish');
                    });
            });
        });

        $route = $this->assertMatch('DELETE', '/api/v1/posts/purge');
        $this->assertSame('App\Http\Controllers\Api\V1\PostController@purge', $route->action['controller']);
        $this->assertSame('v1.posts.purge', $route->getName());

        $route = $this->assertMatch('DELETE', '/api/v1/posts/123');
        $this->assertSame('App\Http\Controllers\Api\V1\PostController@destroy', $route->action['controller']);
        $this->assertSame('v1.posts.destroy', $route->getName());

        $route = $this->assertMatch('POST', '/api/v1/posts/123/publish');
        $this->assertSame('App\Http\Controllers\Api\V1\PostController@publish', $route->action['controller']);
        $this->assertSame('v1.posts.publish', $route->getName());
    }

    /**
     * This is a common scenario that is seen in questions - registering a `me` action
     * on the users resource to return the current signed-in user.
     */
    public function testUserMeDoesNotConflictWithRetrievingUser(): void
    {
        $server = $this->createServer('v1');
        $this->createSchema($server, 'users', '\d+');

        $this->defaultApiRoutes(function () {
            JsonApiRoute::server('v1')->prefix('v1')->resources(function ($server) {
                $server->resource('users', 'App\Http\Controllers\Api\V1\UserController')
                    ->actions(function ($actions) {
                        $actions->get('me');
                    });
            });
        });

        $route = $this->assertMatch('GET', '/api/v1/users/me');
        $this->assertSame('App\Http\Controllers\Api\V1\UserController@me', $route->action['controller']);
        $this->assertSame('v1.users.me', $route->getName());

        $route = $this->assertMatch('GET', '/api/v1/users/1');
        $this->assertSame('App\Http\Controllers\Api\V1\UserController@show', $route->action['controller']);
        $this->assertSame('v1.users.show', $route->getName());
    }
}
