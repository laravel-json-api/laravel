<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace LaravelJsonApi\Laravel;

use Illuminate\Container\Container;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use LaravelJsonApi\Contracts;
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
    }
}
