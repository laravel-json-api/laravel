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

namespace LaravelJsonApi\Laravel;

use Illuminate\Container\Container;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Pipeline\Pipeline;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use LaravelJsonApi\Contracts;
use LaravelJsonApi\Core\Bus\Commands\Dispatcher as CommandDispatcher;
use LaravelJsonApi\Core\Bus\Queries\Dispatcher as QueryDispatcher;
use LaravelJsonApi\Core\Http\Actions\AttachRelationship;
use LaravelJsonApi\Core\Http\Actions\Destroy;
use LaravelJsonApi\Core\Http\Actions\DetachRelationship;
use LaravelJsonApi\Core\Http\Actions\FetchMany;
use LaravelJsonApi\Core\Http\Actions\FetchOne;
use LaravelJsonApi\Core\Http\Actions\FetchRelated;
use LaravelJsonApi\Core\Http\Actions\FetchRelationship;
use LaravelJsonApi\Core\Http\Actions\Store;
use LaravelJsonApi\Core\Http\Actions\Update;
use LaravelJsonApi\Core\Http\Actions\UpdateRelationship;
use LaravelJsonApi\Core\JsonApiService;
use LaravelJsonApi\Core\Server\ServerRepository;
use LaravelJsonApi\Core\Support\AppResolver;
use LaravelJsonApi\Core\Support\ContainerResolver;
use LaravelJsonApi\Laravel\Http\Middleware\BootJsonApi;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Boot application services.
     *
     * @param Router $router
     * @return void
     */
    public function boot(Router $router): void
    {
        $router->aliasMiddleware('jsonapi', BootJsonApi::class);

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/jsonapi.php' => config_path('jsonapi.php'),
            ]);

            $this->commands([
                Console\MakeAuthorizer::class,
                Console\MakeController::class,
                Console\MakeFilter::class,
                Console\MakeQuery::class,
                Console\MakeRequest::class,
                Console\MakeRequests::class,
                Console\MakeResource::class,
                Console\MakeSchema::class,
                Console\MakeServer::class,
                Console\MakeSortField::class,
                Console\StubPublish::class,
            ]);
        }
    }

    /**
     * Register application services.
     */
    public function register(): void
    {
        $this->bindResolvers();
        $this->bindAuthorizer();
        $this->bindService();
        $this->bindServer();
        $this->bindActionsCommandsAndQueries();

        /** @TODO wtf? why isn't it working without this? */
        $this->app->bind(Pipeline::class, \Illuminate\Pipeline\Pipeline::class);

        /** @TODO will need to remove this temporary wiring */
        $this->app->bind(Contracts\Validation\Container::class, Validation\Container::class);
    }

    /**
     * Bind the Octane-compatible lazy instance resolvers into the service container.
     *
     * @return void
     */
    private function bindResolvers(): void
    {
        $this->app->bind(AppResolver::class, static function () {
            return new AppResolver(static fn() => app());
        });

        $this->app->bind(ContainerResolver::class, static function () {
            return new ContainerResolver(static fn() => Container::getInstance());
        });
    }

    /**
     * Bind the authorizer instance into the service container.
     *
     * @return void
     */
    private function bindAuthorizer(): void
    {
        $this->app->bind(Contracts\Auth\Authorizer::class, static function (Application $app) {
            /** @var Contracts\Routing\Route $route */
            $route = $app->make(Contracts\Routing\Route::class);
            return $route->authorizer();
        });
    }

    /**
     * Bind the JSON API service into the service container.
     *
     * @return void
     */
    private function bindService(): void
    {
        $this->app->bind(JsonApiService::class);
    }

    /**
     * Bind server services into the service container.
     *
     * @return void
     */
    private function bindServer(): void
    {
        $this->app->singleton(Contracts\Server\Repository::class, ServerRepository::class);

        $this->app->bind(Contracts\Store\Store::class, static function (Application $app) {
            return $app->make(Contracts\Server\Server::class)->store();
        });

        $this->app->bind(Contracts\Schema\Container::class, static function (Application $app) {
            return $app->make(Contracts\Server\Server::class)->schemas();
        });

        $this->app->bind(Contracts\Resources\Container::class, static function (Application $app) {
            return $app->make(Contracts\Server\Server::class)->resources();
        });

        $this->app->bind(Contracts\Auth\Container::class, static function (Application $app) {
            return $app->make(Contracts\Server\Server::class)->authorizers();
        });
    }

    /**
     * @return void
     */
    private function bindActionsCommandsAndQueries(): void
    {
        /** Actions */
        $this->app->bind(Contracts\Http\Actions\FetchMany::class, FetchMany::class);
        $this->app->bind(Contracts\Http\Actions\FetchOne::class, FetchOne::class);
        $this->app->bind(Contracts\Http\Actions\FetchRelated::class, FetchRelated::class);
        $this->app->bind(Contracts\Http\Actions\FetchRelationship::class, FetchRelationship::class);
        $this->app->bind(Contracts\Http\Actions\Store::class, Store::class);
        $this->app->bind(Contracts\Http\Actions\Update::class, Update::class);
        $this->app->bind(Contracts\Http\Actions\Destroy::class, Destroy::class);
        $this->app->bind(Contracts\Http\Actions\UpdateRelationship::class, UpdateRelationship::class);
        $this->app->bind(Contracts\Http\Actions\AttachRelationship::class, AttachRelationship::class);
        $this->app->bind(Contracts\Http\Actions\DetachRelationship::class, DetachRelationship::class);

        /** Commands */
        $this->app->bind(Contracts\Bus\Commands\Dispatcher::class, CommandDispatcher::class);

        /** Queries */
        $this->app->bind(Contracts\Bus\Queries\Dispatcher::class, QueryDispatcher::class);
    }
}
