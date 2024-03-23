<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace LaravelJsonApi\Laravel\Tests\Acceptance;

use Illuminate\Foundation\Testing\Concerns\InteractsWithDeprecationHandling;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use LaravelJsonApi\Laravel\ServiceProvider;
use LaravelJsonApi\Testing\MakesJsonApiRequests;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    use DatabaseMigrations;
    use MakesJsonApiRequests;
    use InteractsWithDeprecationHandling;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutDeprecationHandling();

        $this->loadLaravelMigrations();
        $this->loadMigrationsFrom(__DIR__ . '/../../dummy/database/migrations');

        config()->set('jsonapi', require __DIR__ . '/../../dummy/config/jsonapi.php');
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
