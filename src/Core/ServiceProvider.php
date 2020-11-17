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

namespace LaravelJsonApi\Core;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use LaravelJsonApi\Contracts;
use LaravelJsonApi\Core\Server\Server;
use LaravelJsonApi\Core\Server\ServerRepository;

class ServiceProvider extends BaseServiceProvider implements DeferrableProvider
{

    /**
     * Register application services.
     *
     * @return void
     */
    public function register()
    {
        $this->bindService();
        $this->bindServer();
    }

    /**
     * @inheritDoc
     */
    public function provides()
    {
        return [
            JsonApiService::class,
            Contracts\Server\Repository::class,
            Contracts\Server\Server::class,
            Contracts\Store\Store::class,
            Contracts\Schema\Container::class,
            Contracts\Resources\Container::class,
        ];
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
        $this->app->bind(Contracts\Server\Server::class, Server::class);

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
