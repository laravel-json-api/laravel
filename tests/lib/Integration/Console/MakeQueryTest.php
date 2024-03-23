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

class MakeQueryTest extends TestCase
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

    public function test(): void
    {
        config()->set('jsonapi.servers', [
            'v1' => Server::class,
        ]);

        $result = $this->artisan('jsonapi:query posts');

        $this->assertSame(0, $result);
        $this->assertQueryCreated();
        $this->assertQueryCollectionNotCreated();
    }

    public function testCollection(): void
    {
        config()->set('jsonapi.servers', [
            'v1' => Server::class,
        ]);

        $result = $this->artisan('jsonapi:query posts --collection');

        $this->assertSame(0, $result);
        $this->assertQueryCollectionCreated();
        $this->assertQueryNotCreated();
    }

    public function testBoth(): void
    {
        config()->set('jsonapi.servers', [
            'v1' => Server::class,
        ]);

        $result = $this->artisan('jsonapi:query posts --both');

        $this->assertSame(0, $result);
        $this->assertQueryCreated();
        $this->assertQueryCollectionCreated();
    }

    public function testCollectionAndBoth(): void
    {
        config()->set('jsonapi.servers', [
            'v1' => Server::class,
        ]);

        $result = $this->artisan('jsonapi:query posts --both --collection');

        $this->assertSame(0, $result);
        $this->assertQueryCreated();
        $this->assertQueryCollectionCreated();
    }

    public function testServer(): void
    {
        config()->set('jsonapi.servers', [
            'beta' => 'App\JsonApi\Beta\Server',
            'v1' => Server::class,
        ]);

        $result = $this->artisan('jsonapi:query posts --server v1');

        $this->assertSame(0, $result);
        $this->assertQueryCreated();
    }

    public function testNoServer(): void
    {
        config()->set('jsonapi.servers', [
            'beta' => 'App\JsonApi\Beta\Server',
            'v1' => Server::class,
        ]);

        $result = $this->artisan('jsonapi:query', [
            'name' => 'posts',
        ]);

        $this->assertSame(1, $result);
        $this->assertQueryNotCreated();
    }

    public function testInvalidServer(): void
    {
        config()->set('jsonapi.servers', [
            'v1' => Server::class,
        ]);

        $result = $this->artisan('jsonapi:query', [
            'name' => 'posts',
            '--server' => 'v2',
        ]);

        $this->assertSame(1, $result);
        $this->assertQueryNotCreated();
    }

    /**
     * @return void
     */
    private function assertQueryCreated(): void
    {
        $this->assertFileExists($path = app_path('JsonApi/V1/Posts/PostQuery.php'));
        $content = file_get_contents($path);

        $tests = [
            'namespace App\JsonApi\V1\Posts',
            'use LaravelJsonApi\Laravel\Http\Requests\ResourceQuery;',
            'use LaravelJsonApi\Validation\Rule as JsonApiRule;',
            'class PostQuery extends ResourceQuery',
            "'page' => JsonApiRule::notSupported(),",
            "'sort' => JsonApiRule::notSupported(),",
            "'withCount' =>",
        ];

        foreach ($tests as $expected) {
            $this->assertStringContainsString($expected, $content);
        }
    }

    /**
     * @return void
     */
    private function assertQueryCollectionCreated(): void
    {
        $this->assertFileExists($path = app_path('JsonApi/V1/Posts/PostCollectionQuery.php'));
        $content = file_get_contents($path);

        $tests = [
            'namespace App\JsonApi\V1\Posts',
            'use LaravelJsonApi\Laravel\Http\Requests\ResourceQuery;',
            'use LaravelJsonApi\Validation\Rule as JsonApiRule;',
            'class PostCollectionQuery extends ResourceQuery',
            'JsonApiRule::page(),',
            'JsonApiRule::sort(),',
            "'withCount' =>",
        ];

        foreach ($tests as $expected) {
            $this->assertStringContainsString($expected, $content);
        }
    }

    /**
     * @return void
     */
    private function assertQueryNotCreated(): void
    {
        $this->assertFileDoesNotExist(app_path('JsonApi/V1/Posts/PostQuery.php'));
    }

    /**
     * @return void
     */
    private function assertQueryCollectionNotCreated(): void
    {
        $this->assertFileDoesNotExist(app_path('JsonApi/V1/Posts/PostCollectionQuery.php'));
    }
}
