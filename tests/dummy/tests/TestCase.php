<?php

declare(strict_types=1);

namespace DummyApp\Tests;

use DummyApp\Providers\AppServiceProvider;
use DummyApp\Providers\AuthServiceProvider;
use DummyApp\Providers\EventServiceProvider;
use DummyApp\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use LaravelJsonApi\ServiceProvider;
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
        $this->withFactories(__DIR__ . '/../database/factories');

        config()->set('json-api', require __DIR__ . '/../config/json-api.php');
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     * @return array|string[]
     */
    protected function getPackageProviders($app)
    {
        return [
            ServiceProvider::class,
            AppServiceProvider::class,
            AuthServiceProvider::class,
            EventServiceProvider::class,
            RouteServiceProvider::class,
        ];
    }
}
