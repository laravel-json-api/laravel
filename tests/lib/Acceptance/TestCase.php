<?php
/*
 * Copyright 2021 Cloud Creativity Limited
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

namespace LaravelJsonApi\Laravel\Tests\Acceptance;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use LaravelJsonApi\Laravel\ServiceProvider;
use LaravelJsonApi\Testing\MakesJsonApiRequests;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Vinkla\Hashids\HashidsServiceProvider;

class TestCase extends BaseTestCase
{

    use DatabaseMigrations;
    use MakesJsonApiRequests;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations();
        $this->loadMigrationsFrom(__DIR__ . '/../../dummy/database/migrations');

        config()->set('jsonapi', require __DIR__ . '/../../dummy/config/jsonapi.php');

        config()->set('hashids', [
            'default' => 'main',
            'connections' => [
                'main' => [
                    'salt' => 'Z3wxm8m6fxPMRtjX',
                    'length' => 10,
                ],
            ],
        ]);
    }

    /**
     * @inheritDoc
     */
    protected function getPackageProviders($app)
    {
        return [
            \LaravelJsonApi\Spec\ServiceProvider::class,
            \LaravelJsonApi\Validation\ServiceProvider::class,
            \LaravelJsonApi\Encoder\Neomerx\ServiceProvider::class,
            HashidsServiceProvider::class,
            ServiceProvider::class,
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
