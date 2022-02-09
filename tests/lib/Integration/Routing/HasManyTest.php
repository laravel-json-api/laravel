<?php
/*
 * Copyright 2022 Cloud Creativity Limited
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

use Illuminate\Contracts\Routing\Registrar;
use LaravelJsonApi\Core\Support\Arr;
use LaravelJsonApi\Laravel\Facades\JsonApiRoute;

class HasManyTest extends TestCase
{

    /**
     * @return array
     */
    public function genericProvider(): array
    {
        return [
            'showRelated' => [
                'GET',
                '/api/v1/posts/123/tags',
                'showRelated',
                'tags',
            ],
            'showRelationship' => [
                'GET',
                '/api/v1/posts/123/relationships/tags',
                'showRelationship',
                'tags.show',
            ],
            'updateRelationship' => [
                'PATCH',
                '/api/v1/posts/123/relationships/tags',
                'updateRelationship',
                'tags.update',
            ],
            'attachRelationship' => [
                'POST',
                '/api/v1/posts/123/relationships/tags',
                'attachRelationship',
                'tags.attach',
            ],
            'detachRelationship' => [
                'DELETE',
                '/api/v1/posts/123/relationships/tags',
                'detachRelationship',
                'tags.detach',
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
        $schema = $this->createSchema($server, 'posts', '\d+');
        $this->createRelation($schema, 'tags');

        $this->defaultApiRoutesWithNamespace(function () {
            JsonApiRoute::server('v1')->prefix('v1')->namespace('Api\\V1')->resources(function ($server) {
                $server->resource('posts')->relationships(function ($relations, $routes) {
                    $this->assertInstanceOf(Registrar::class, $routes);
                    $relations->hasMany('tags');
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
        $this->assertSame('tags', $route->parameter('resource_relationship'));
    }


    /**
     * @param string $method
     * @param string $uri
     * @param string $action
     * @dataProvider genericProvider
     */
    public function testName(string $method, string $uri, string $action): void
    {
        $server = $this->createServer('v1');
        $schema = $this->createSchema($server, 'posts', '\d+');
        $this->createRelation($schema, 'tags');

        $this->defaultApiRoutesWithNamespace(function () use ($action) {
            JsonApiRoute::server('v1')
                ->prefix('v1')
                ->namespace('Api\\V1')
                ->name('my-api:')
                ->resources(function ($server) use ($action) {
                    $server->resource('posts')->relationships(function ($relations) use ($action) {
                        $relations->hasMany('tags')->name($action, 'foobar.bazbat');
                    });
                });
        });

        $route = $this->assertMatch($method, $uri);
        $this->assertSame("my-api:posts.foobar.bazbat", $route->getName());
    }

    /**
     * @param string $method
     * @param string $uri
     * @dataProvider genericProvider
     */
    public function testMiddleware(string $method, string $uri): void
    {
        $server = $this->createServer('v1');
        $schema = $this->createSchema($server, 'posts', '\d+');
        $this->createRelation($schema, 'tags');

        $this->defaultApiRoutesWithNamespace(function () {
            JsonApiRoute::server('v1')
                ->prefix('v1')
                ->namespace('Api\\V1')
                ->middleware('foo')
                ->resources(function ($server) {
                    $server->resource('posts')->middleware('bar')->relationships(function ($relations) {
                        $relations->hasMany('tags')->middleware('baz');
                    });
                });
        });

        $route = $this->assertMatch($method, $uri);
        $this->assertSame(['api', 'jsonapi:v1', 'foo', 'bar', 'baz'], $route->action['middleware']);
    }

    /**
     * @param string $method
     * @param string $uri
     * @dataProvider genericProvider
     */
    public function testUri(string $method, string $uri): void
    {
        $server = $this->createServer('v1');
        $schema = $this->createSchema($server, 'posts', '\d+');
        $this->createRelation($schema, 'blog-tags', 'tags');

        $this->defaultApiRoutesWithNamespace(function () {
            JsonApiRoute::server('v1')
                ->prefix('v1')
                ->namespace('Api\\V1')
                ->resources(function ($server) {
                    $server->resource('posts')->relationships(function ($relations) {
                        $relations->hasMany('blog-tags');
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
                ['GET', '/api/v1/posts/1/tags', 200],
                ['GET', '/api/v1/posts/1/relationships/tags', 404],
                ['PATCH', '/api/v1/posts/1/relationships/tags', 404],
                ['POST', '/api/v1/posts/1/relationships/tags', 404],
                ['DELETE', '/api/v1/posts/1/relationships/tags', 404],
            ]],
            [['related', 'show'], [
                ['GET', '/api/v1/posts/1/tags', 200],
                ['GET', '/api/v1/posts/1/relationships/tags', 200],
                ['PATCH', '/api/v1/posts/1/relationships/tags', 405],
                ['POST', '/api/v1/posts/1/relationships/tags', 405],
                ['DELETE', '/api/v1/posts/1/relationships/tags', 405],
            ]],
            ['update', [
                ['GET', '/api/v1/posts/1/tags', 404],
                ['GET', '/api/v1/posts/1/relationships/tags', 405],
                ['PATCH', '/api/v1/posts/1/relationships/tags', 200],
                ['POST', '/api/v1/posts/1/relationships/tags', 405],
                ['DELETE', '/api/v1/posts/1/relationships/tags', 405],
            ]],
            // the old package used 'replace' instead of 'update'
            ['replace', [
                ['GET', '/api/v1/posts/1/tags', 404],
                ['GET', '/api/v1/posts/1/relationships/tags', 405],
                ['PATCH', '/api/v1/posts/1/relationships/tags', 200],
                ['POST', '/api/v1/posts/1/relationships/tags', 405],
                ['DELETE', '/api/v1/posts/1/relationships/tags', 405],
            ]],
            [['attach', 'detach'], [
                ['GET', '/api/v1/posts/1/tags', 404],
                ['GET', '/api/v1/posts/1/relationships/tags', 405],
                ['PATCH', '/api/v1/posts/1/relationships/tags', 405],
                ['POST', '/api/v1/posts/1/relationships/tags', 200],
                ['DELETE', '/api/v1/posts/1/relationships/tags', 200],
            ]],
            // the old package used 'add' and 'remove' instead of 'attach' and 'detach'
            [['add', 'remove'], [
                ['GET', '/api/v1/posts/1/tags', 404],
                ['GET', '/api/v1/posts/1/relationships/tags', 405],
                ['PATCH', '/api/v1/posts/1/relationships/tags', 405],
                ['POST', '/api/v1/posts/1/relationships/tags', 200],
                ['DELETE', '/api/v1/posts/1/relationships/tags', 200],
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
        $schema = $this->createSchema($server, 'posts', '\d+');
        $this->createRelation($schema, 'tags');

        $this->defaultApiRoutesWithNamespace(function () use ($only) {
            JsonApiRoute::server('v1')
                ->prefix('v1')
                ->namespace('Api\\V1')
                ->resources(function ($server) use ($only) {
                    $server->resource('posts')->relationships(function ($relations) use ($only) {
                        $relations->hasMany('tags')->only(...Arr::wrap($only));
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
                ['GET', '/api/v1/posts/1/tags', 404],
                ['GET', '/api/v1/posts/1/relationships/tags', 200],
                ['PATCH', '/api/v1/posts/1/relationships/tags', 200],
                ['POST', '/api/v1/posts/1/relationships/tags', 200],
                ['DELETE', '/api/v1/posts/1/relationships/tags', 200],
            ]],
            [['related', 'show'], [
                ['GET', '/api/v1/posts/1/tags', 404],
                ['GET', '/api/v1/posts/1/relationships/tags', 405],
                ['PATCH', '/api/v1/posts/1/relationships/tags', 200],
                ['POST', '/api/v1/posts/1/relationships/tags', 200],
                ['DELETE', '/api/v1/posts/1/relationships/tags', 200],
            ]],
            ['update', [
                ['GET', '/api/v1/posts/1/tags', 200],
                ['GET', '/api/v1/posts/1/relationships/tags', 200],
                ['PATCH', '/api/v1/posts/1/relationships/tags', 405],
                ['POST', '/api/v1/posts/1/relationships/tags', 200],
                ['DELETE', '/api/v1/posts/1/relationships/tags', 200],
            ]],
            // the old package used 'replace' instead of 'update'
            ['replace', [
                ['GET', '/api/v1/posts/1/tags', 200],
                ['GET', '/api/v1/posts/1/relationships/tags', 200],
                ['PATCH', '/api/v1/posts/1/relationships/tags', 405],
                ['POST', '/api/v1/posts/1/relationships/tags', 200],
                ['DELETE', '/api/v1/posts/1/relationships/tags', 200],
            ]],
            [['attach', 'detach'], [
                ['GET', '/api/v1/posts/1/tags', 200],
                ['GET', '/api/v1/posts/1/relationships/tags', 200],
                ['PATCH', '/api/v1/posts/1/relationships/tags', 200],
                ['POST', '/api/v1/posts/1/relationships/tags', 405],
                ['DELETE', '/api/v1/posts/1/relationships/tags', 405],
            ]],
            // the old package used 'add' and 'remove' instead of 'attach' and 'detach'
            [['add', 'remove'], [
                ['GET', '/api/v1/posts/1/tags', 200],
                ['GET', '/api/v1/posts/1/relationships/tags', 200],
                ['PATCH', '/api/v1/posts/1/relationships/tags', 200],
                ['POST', '/api/v1/posts/1/relationships/tags', 405],
                ['DELETE', '/api/v1/posts/1/relationships/tags', 405],
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
        $schema = $this->createSchema($server, 'posts', '\d+');
        $this->createRelation($schema, 'tags');

        $this->defaultApiRoutesWithNamespace(function () use ($except) {
            JsonApiRoute::server('v1')
                ->prefix('v1')
                ->namespace('Api\\V1')
                ->resources(function ($server) use ($except) {
                    $server->resource('posts')->relationships(function ($relations) use ($except) {
                        $relations->hasMany('tags')->except(...Arr::wrap($except));
                    });
                });
        });

        $this->assertRoutes($matches);
    }

    public function testReadOnly(): void
    {
        $server = $this->createServer('v1');
        $schema = $this->createSchema($server, 'posts', '\d+');
        $this->createRelation($schema, 'tags');

        $this->defaultApiRoutesWithNamespace(function () {
            JsonApiRoute::server('v1')
                ->prefix('v1')
                ->namespace('Api\\V1')
                ->resources(function ($server) {
                    $server->resource('posts')->relationships(function ($relations) {
                        $relations->hasMany('tags')->readOnly();
                    });
                });
        });

        $this->assertRoutes([
            ['GET', '/api/v1/posts/1/tags', 200],
            ['GET', '/api/v1/posts/1/relationships/tags', 200],
            ['PATCH', '/api/v1/posts/1/relationships/tags', 405],
            ['POST', '/api/v1/posts/1/relationships/tags', 405],
            ['DELETE', '/api/v1/posts/1/relationships/tags', 405],
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
                '/api/v1/posts/123/tags',
                'related',
                'showRelatedTags',
            ],
            'showRelationship' => [
                'GET',
                '/api/v1/posts/123/relationships/tags',
                'show',
                'showTags',
            ],
            'updateRelationship' => [
                'PATCH',
                '/api/v1/posts/123/relationships/tags',
                'update',
                'updateTags',
            ],
            'attachRelationship' => [
                'POST',
                '/api/v1/posts/123/relationships/tags',
                'attach',
                'attachTags',
            ],
            'detachRelationship' => [
                'DELETE',
                '/api/v1/posts/123/relationships/tags',
                'detach',
                'detachTags',
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
        $schema = $this->createSchema($server, 'posts', '\d+');
        $this->createRelation($schema, 'tags');

        $this->defaultApiRoutesWithNamespace(function () use ($action) {
            JsonApiRoute::server('v1')->prefix('v1')->namespace('Api\\V1')->resources(function ($server) use ($action) {
                $server->resource('posts')->relationships(function ($relations) use ($action) {
                    $relations->hasMany('tags')->ownAction($action);
                });
            });
        });

        $route = $this->assertMatch($method, $uri);
        $this->assertSame("App\Http\Controllers\Api\V1\PostController@{$expected}", $route->action['controller']);
        $this->assertSame('posts', $route->parameter('resource_type'));
        $this->assertSame('post', $route->parameter('resource_id_name'));
        $this->assertSame('tags', $route->parameter('resource_relationship'));
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
        $schema = $this->createSchema($server, 'posts', '\d+');
        $this->createRelation($schema, 'tags');

        $this->defaultApiRoutesWithNamespace(function () {
            JsonApiRoute::server('v1')->prefix('v1')->namespace('Api\\V1')->resources(function ($server) {
                $server->resource('posts')->relationships(function ($relations) {
                    $relations->hasMany('tags')->ownActions();
                });
            });
        });

        $route = $this->assertMatch($method, $uri);
        $this->assertSame("App\Http\Controllers\Api\V1\PostController@{$expected}", $route->action['controller']);
        $this->assertSame('posts', $route->parameter('resource_type'));
        $this->assertSame('post', $route->parameter('resource_id_name'));
        $this->assertSame('tags', $route->parameter('resource_relationship'));
    }

    /**
     * @param string $method
     * @param string $uri
     * @dataProvider genericProvider
     */
    public function testIdConstraintWorks(string $method, string $uri): void
    {
        $server = $this->createServer('v1');
        $schema = $this->createSchema($server, 'posts', '\d+');
        $this->createRelation($schema, 'tags');

        $this->defaultApiRoutesWithNamespace(function () {
            JsonApiRoute::server('v1')
                ->prefix('v1')
                ->namespace('Api\\V1')
                ->resources(function ($server) {
                    $server->resource('posts')->relationships(function ($relations) {
                        $relations->hasMany('tags');
                    });
                });
        });

        $this->assertMatch($method, $uri);
        $this->assertNotFound($method, str_replace('123', '123abc', $uri));
    }
}
