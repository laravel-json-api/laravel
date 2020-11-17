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

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use LaravelJsonApi\Contracts;
use LaravelJsonApi\Encoder\Neomerx\Factory as EncoderFactory;
use LaravelJsonApi\Http\Middleware\BootJsonApi;
use LaravelJsonApi\Http\Middleware\SubstituteBindings;
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
        $router->aliasMiddleware('json-api.bindings', SubstituteBindings::class);
    }

    /**
     * Register application services.
     */
    public function register(): void
    {
        $this->app->registerDeferredProvider(Core\ServiceProvider::class);
        $this->bindEncoder();
        $this->bindRoute();
        $this->bindSpecification();
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
     * Bind the route instance into the container.
     *
     * @return void
     */
    private function bindRoute(): void
    {
        $this->app->bind(Contracts\Routing\Route::class, Route::class);
    }

    /**
     * Bind the JSON API specification into the service container.
     *
     * @return void
     */
    private function bindSpecification(): void
    {
        $this->app->bind(Spec\Specification::class, Spec\ServerSpecification::class);
        $this->app->singleton(Spec\Translator::class);
    }
}
