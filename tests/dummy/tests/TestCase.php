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

namespace App\Tests;

use App\Providers\AppServiceProvider;
use App\Providers\AuthServiceProvider;
use App\Providers\EventServiceProvider;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use LaravelJsonApi\Laravel\ServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{

    use DatabaseMigrations;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadLaravelMigrations();
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     * @return array|string[]
     */
    protected function getPackageProviders($app)
    {
        return [
            \LaravelJsonApi\Spec\ServiceProvider::class,
            \LaravelJsonApi\Validation\ServiceProvider::class,
            \LaravelJsonApi\Encoder\Neomerx\ServiceProvider::class,
            ServiceProvider::class,
            AppServiceProvider::class,
            AuthServiceProvider::class,
            EventServiceProvider::class,
            RouteServiceProvider::class,
        ];
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function resolveApplicationExceptionHandler($app)
    {
        $app->singleton(
            \Illuminate\Contracts\Debug\ExceptionHandler::class,
            \LaravelJsonApi\Testing\TestExceptionHandler::class
        );
    }
}
