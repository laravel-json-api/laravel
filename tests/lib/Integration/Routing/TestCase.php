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

use Illuminate\Http\Request;
use Illuminate\Routing\Route as IlluminateRoute;
use Illuminate\Support\Facades\Route;
use LaravelJsonApi\Contracts\Schema\Container;
use LaravelJsonApi\Contracts\Schema\ID;
use LaravelJsonApi\Contracts\Schema\Relation;
use LaravelJsonApi\Contracts\Schema\Schema;
use LaravelJsonApi\Contracts\Server\Repository;
use LaravelJsonApi\Contracts\Server\Server;
use LaravelJsonApi\Laravel\Tests\Integration\TestCase as BaseTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TestCase extends BaseTestCase
{

    /**
     * @var Repository|MockObject
     */
    protected Repository $servers;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->app->instance(Repository::class, $this->servers = $this->createMock(Repository::class));
    }

    /**
     * @param string $name
     * @return Server|MockObject
     */
    protected function createServer(string $name): Server
    {
        $mock = $this->createMock(Server::class);
        $mock->method('name')->willReturn($name);
        $this->servers->method('server')->with($name)->willReturn($mock);

        return $mock;
    }

    /**
     * @param Server|MockObject $server
     * @param string $name
     * @param string|null $pattern
     * @param string|null $uriType
     * @return Schema|MockObject
     */
    protected function createSchema(
        Server $server,
        string $name,
        string $pattern = null,
        string $uriType = null
    ): Schema
    {
        $schema = $this->createMock(Schema::class);
        $schema->method('uriType')->willReturn($uriType ?: $name);
        $schema->method('id')->willReturn($id = $this->createMock(ID::class));
        $id->method('pattern')->willReturn($pattern ?: '[0-9]+');

        $schemas = $this->createMock(Container::class);
        $schemas->method('schemaFor')->with($name)->willReturn($schema);

        $server->method('schemas')->willReturn($schemas);

        return $schema;
    }

    /**
     * @param MockObject $schema
     * @param string $fieldName
     * @param string|null $uriName
     * @return void
     */
    protected function createRelation(MockObject $schema, string $fieldName, string $uriName = null): void
    {
        $relation = $this->createMock(Relation::class);
        $relation->method('name')->willReturn($fieldName);
        $relation->method('uriName')->willReturn($uriName ?: $fieldName);

        $schema->method('relationship')->with($fieldName)->willReturn($relation);
    }

    /**
     * @param string $method
     * @param string $url
     * @return IlluminateRoute
     */
    protected function assertMatch(string $method, string $url): IlluminateRoute
    {
        $request = $this->createRequest($method, $url);
        $route = null;

        try {
            $route = Route::getRoutes()->match($request);
            $matched = true;
        } catch (NotFoundHttpException $e) {
            $matched = false;
        } catch (MethodNotAllowedHttpException $e) {
            $matched = false;
        }

        $this->assertTrue($matched, "Route $method $url did not match.");

        return $route;
    }

    /**
     * @param string $method
     * @param string $url
     * @return void
     */
    protected function assertMethodNotAllowed(string $method, string $url): void
    {
        $request = $this->createRequest($method, $url);
        $notAllowed = false;

        try {
            Route::getRoutes()->match($request);
        } catch (MethodNotAllowedHttpException $e) {
            $notAllowed = true;
        }

        $this->assertTrue($notAllowed, "Route $method $url is allowed");
    }

    /**
     * @param string $method
     * @param string $url
     * @return void
     */
    protected function assertNotFound(string $method, string $url): void
    {
        $request = $this->createRequest($method, $url);
        $notFound = false;

        try {
            Route::getRoutes()->match($request);
        } catch (NotFoundHttpException $e) {
            $notFound = true;
        }

        $this->assertTrue($notFound, "Route $method $url is found");
    }

    /**
     * @param string $method
     * @param string $url
     * @param int $expected
     * @return void
     */
    protected function assertRoute(string $method, string $url, int $expected = 200): void
    {
        if (405 === $expected) {
            $this->assertMethodNotAllowed($method, $url);
        } elseif (404 === $expected) {
            $this->assertNotFound($method, $url);
        } else {
            $this->assertMatch($method, $url);
        }
    }

    /**
     * @param array $routes
     * @return void
     */
    protected function assertRoutes(array $routes): void
    {
        foreach ($routes as [$method, $url, $expected]) {
            $this->assertRoute($method, $url, $expected);
        }
    }

    /**
     * @param string $method
     * @param string $url
     * @return Request
     */
    protected function createRequest(string $method, string $url): Request
    {
        return Request::create($url, $method);
    }

    /**
     * Call the closure within the default Laravel API route setup.
     *
     * @param \Closure $callback
     * @return void
     * @see https://github.com/laravel/laravel/blob/8.x/app/Providers/RouteServiceProvider.php
     */
    protected function defaultApiRoutes(\Closure $callback): void
    {
        Route::prefix('api')
            ->middleware('api')
            ->group($callback);
    }

    /**
     * Call the closure within the default Laravel API route setup.
     *
     * @param \Closure $callback
     * @return void
     * @see https://github.com/laravel/laravel/blob/8.x/app/Providers/RouteServiceProvider.php
     */
    protected function defaultApiRoutesWithNamespace(\Closure $callback): void
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace('App\\Http\\Controllers')
            ->group($callback);
    }
}
