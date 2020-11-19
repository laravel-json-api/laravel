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

namespace LaravelJsonApi\Laravel;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use LaravelJsonApi\Contracts;
use LaravelJsonApi\Core\JsonApiService;
use LaravelJsonApi\Core\Server\ServerRepository;
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
        $router->aliasMiddleware('json-api', BootJsonApi::class);
    }

    /**
     * Register application services.
     */
    public function register(): void
    {
        $this->bindService();
        $this->bindServer();
    }

    /**
     * Bind the JSON API service into the service container.
     *
     * @return void
     */
    private function bindService(): void
    {
        $this->app->singleton(JsonApiService::class);
    }

    /**
     * Bind server services into the service container.
     *
     * @return void
     */
    private function bindServer(): void
    {
        $this->app->bind(Contracts\Server\Repository::class, ServerRepository::class);

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
