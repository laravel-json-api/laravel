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
use LaravelJsonApi\Core\Contracts\Resources\Container as ResourceContainer;
use LaravelJsonApi\Core\Contracts\Schema\Container as SchemaContainer;
use LaravelJsonApi\Core\Store\Store;
use LaravelJsonApi\Http\Middleware\BootJsonApi;
use LaravelJsonApi\Http\Server;

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
        $this->app->bind(Store::class, static function (Application $app) {
            return $app->make(Server::class)->store();
        });

        $this->app->bind(SchemaContainer::class, static function (Application $app) {
            return $app->make(Server::class)->container();
        });

        $this->app->bind(ResourceContainer::class, static function (Application $app) {
            return $app->make(Server::class)->resources();
        });

        $this->app->singleton(JsonApiService::class);
        $this->app->alias(JsonApiService::class, 'json-api');
    }
}
