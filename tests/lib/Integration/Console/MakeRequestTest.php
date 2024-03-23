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

class MakeRequestTest extends TestCase
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

        $result = $this->artisan('jsonapi:request posts');

        $this->assertSame(0, $result);
        $this->assertRequestCreated();
    }

    public function testServer(): void
    {
        config()->set('jsonapi.servers', [
            'beta' => 'App\JsonApi\Beta\Server',
            'v1' => Server::class,
        ]);

        $result = $this->artisan('jsonapi:request posts --server v1');

        $this->assertSame(0, $result);
        $this->assertRequestCreated();
    }

    public function testNoServer(): void
    {
        config()->set('jsonapi.servers', [
            'beta' => 'App\JsonApi\Beta\Server',
            'v1' => Server::class,
        ]);

        $result = $this->artisan('jsonapi:request', [
            'name' => 'posts',
        ]);

        $this->assertSame(1, $result);
        $this->assertRequestNotCreated();
    }

    public function testInvalidServer(): void
    {
        config()->set('jsonapi.servers', [
            'v1' => Server::class,
        ]);

        $result = $this->artisan('jsonapi:request', [
            'name' => 'posts',
            '--server' => 'v2',
        ]);

        $this->assertSame(1, $result);
        $this->assertRequestNotCreated();
    }

    /**
     * @return void
     */
    private function assertRequestCreated(): void
    {
        $this->assertFileExists($path = app_path('JsonApi/V1/Posts/PostRequest.php'));
        $content = file_get_contents($path);

        $tests = [
            'namespace App\JsonApi\V1\Posts',
            'use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;',
            'class PostRequest extends ResourceRequest',
        ];

        foreach ($tests as $expected) {
            $this->assertStringContainsString($expected, $content);
        }
    }

    /**
     * @return void
     */
    private function assertRequestNotCreated(): void
    {
        $this->assertFileDoesNotExist(app_path('JsonApi/V1/Posts/PostRequest.php'));
    }
}
