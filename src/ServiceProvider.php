<?php
/**
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

namespace LaravelJsonApi;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use LaravelJsonApi\Contracts;
use LaravelJsonApi\Core\JsonApiService;
use LaravelJsonApi\Encoder\Neomerx\Factory as EncoderFactory;
use LaravelJsonApi\Http\Middleware\BootJsonApi;
use LaravelJsonApi\Http\Server;
use LaravelJsonApi\Http\ServerRepository;
use LaravelJsonApi\Routing\Route;
use Neomerx\JsonApi\Contracts\Factories\FactoryInterface;
use Neomerx\JsonApi\Factories\Factory as NeomerxFactory;

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
        $router->aliasMiddleware('json-api', BootJsonApi::class);
    }

    /**
     * Register application services.
     */
    public function register(): void
    {
        $this->bindEncoder();
        $this->bindHttp();
        $this->bindService();
        $this->bindRoute();
    }

    /**
     * Bind the encoder into the service container.
     *
     * @return void
     */
    private function bindEncoder(): void
    {
        $this->app->bind(Contracts\Encoder\Factory::class, EncoderFactory::class);
        $this->app->bind(FactoryInterface::class, NeomerxFactory::class);
    }

    /**
     * Bind HTTP services into the service container.
     *
     * @return void
     */
    private function bindHttp(): void
    {
        $this->app->bind(Contracts\Http\Repository::class, ServerRepository::class);
        $this->app->bind(Contracts\Http\Server::class, Server::class);

        $this->app->bind(Contracts\Store\Store::class, static function (Application $app) {
            return $app->make(Contracts\Http\Server::class)->store();
        });

        $this->app->bind(Contracts\Schema\Container::class, static function (Application $app) {
            return $app->make(Contracts\Http\Server::class)->schemas();
        });

        $this->app->bind(Contracts\Resources\Container::class, static function (Application $app) {
            return $app->make(Contracts\Http\Server::class)->resources();
        });
    }

    /**
     * Bind the route instance into the container.
     *
     * @return void
     */
    private function bindRoute(): void
    {
        $this->app->bind(Contracts\Routing\Route::class, Route::class);
    }

    /**
     * Bind the JSON API service into the service container.
     *
     * @return void
     */
    private function bindService(): void
    {
        $this->app->singleton(JsonApiService::class);
        $this->app->alias(JsonApiService::class, 'json-api');
    }
}
