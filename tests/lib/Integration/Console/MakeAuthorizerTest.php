<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace LaravelJsonApi\Laravel\Tests\Integration\Console;

use App\JsonApi\V1\Server;
use Illuminate\Filesystem\Filesystem;
use LaravelJsonApi\Laravel\Tests\Integration\TestCase;

class MakeAuthorizerTest extends TestCase
{

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMockingConsoleOutput();

        $files = new Filesystem();
        $files->deleteDirectory(app_path('JsonApi'));
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $files = new Filesystem();
        $files->deleteDirectory(app_path('JsonApi'));
    }

    public function testGeneric(): void
    {
        config()->set('jsonapi.servers', [
            'v1' => Server::class,
        ]);

        $result = $this->artisan('jsonapi:authorizer blog');

        $this->assertSame(0, $result);
        $this->assertGenericAuthorizerCreated();
    }

    /**
     * As a generic authorizer is not created in a server namespace, the
     * developer shouldn't have to provide a server argument even if there
     * are multiple servers.
     *
     * @see https://github.com/laravel-json-api/laravel/issues/34
     */
    public function testGenericWithMultipleServers(): void
    {
        config()->set('jsonapi.servers', [
            'beta' => 'App\JsonApi\Beta\Server',
            'v1' => Server::class,
        ]);

        $result = $this->artisan('jsonapi:authorizer blog');

        $this->assertSame(0, $result);
        $this->assertGenericAuthorizerCreated();
    }

    public function testResource(): void
    {
        config()->set('jsonapi.servers', [
            'v1' => Server::class,
        ]);

        $result = $this->artisan('jsonapi:authorizer posts --resource');

        $this->assertSame(0, $result);
        $this->assertResourceAuthorizerCreated();
    }

    public function testResourceWithServer(): void
    {
        config()->set('jsonapi.servers', [
            'beta' => 'App\JsonApi\Beta\Server',
            'v1' => Server::class,
        ]);

        $result = $this->artisan('jsonapi:authorizer posts --resource --server v1');

        $this->assertSame(0, $result);
        $this->assertResourceAuthorizerCreated();
    }

    public function testNoServer(): void
    {
        config()->set('jsonapi.servers', [
            'beta' => 'App\JsonApi\Beta\Server',
            'v1' => Server::class,
        ]);

        $result = $this->artisan('jsonapi:authorizer', [
            'name' => 'posts',
            '--resource' => true,
        ]);

        $this->assertSame(1, $result);
        $this->assertAuthorizerNotCreated();
    }

    public function testInvalidServer(): void
    {
        config()->set('jsonapi.servers', [
            'v1' => Server::class,
        ]);

        $result = $this->artisan('jsonapi:authorizer', [
            'name' => 'posts',
            '--server' => 'v2',
            '--resource' => true,
        ]);

        $this->assertSame(1, $result);
        $this->assertAuthorizerNotCreated();
    }

    /**
     * @return void
     */
    private function assertGenericAuthorizerCreated(): void
    {
        $this->assertFileExists($path = app_path('JsonApi/Authorizers/BlogAuthorizer.php'));
        $content = file_get_contents($path);

        $tests = [
            'namespace App\JsonApi\Authorizers;',
            'use LaravelJsonApi\Contracts\Auth\Authorizer;',
            'class BlogAuthorizer implements Authorizer',
        ];

        foreach ($tests as $expected) {
            $this->assertStringContainsString($expected, $content);
        }
    }

    /**
     * @return void
     */
    private function assertResourceAuthorizerCreated(): void
    {
        $this->assertFileExists($path = app_path('JsonApi/V1/Posts/PostAuthorizer.php'));
        $content = file_get_contents($path);

        $tests = [
            'namespace App\JsonApi\V1\Posts;',
            'use LaravelJsonApi\Contracts\Auth\Authorizer;',
            'class PostAuthorizer implements Authorizer',
        ];

        foreach ($tests as $expected) {
            $this->assertStringContainsString($expected, $content);
        }
    }

    /**
     * @return void
     */
    private function assertAuthorizerNotCreated(): void
    {
        $this->assertFileDoesNotExist(app_path('JsonApi/V1/BlogAuthorizer.php'));
        $this->assertFileDoesNotExist(app_path('JsonApi/V1/Posts/PostAuthorizer.php'));
    }
}
