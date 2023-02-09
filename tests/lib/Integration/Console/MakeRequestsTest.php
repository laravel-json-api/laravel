<?php
/*
 * Copyright 2023 Cloud Creativity Limited
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

namespace LaravelJsonApi\Laravel\Tests\Integration\Console;

use App\JsonApi\V1\Server;
use Illuminate\Filesystem\Filesystem;
use LaravelJsonApi\Laravel\Tests\Integration\TestCase;

class MakeRequestsTest extends TestCase
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

        $result = $this->artisan('jsonapi:requests posts');

        $this->assertSame(0, $result);
        $this->assertAllCreated();
    }

    public function testServer(): void
    {
        config()->set('jsonapi.servers', [
            'beta' => 'App\JsonApi\Beta\Server',
            'v1' => Server::class,
        ]);

        $result = $this->artisan('jsonapi:requests posts --server v1');

        $this->assertSame(0, $result);
        $this->assertAllCreated();
    }

    public function testNoServer(): void
    {
        config()->set('jsonapi.servers', [
            'beta' => 'App\JsonApi\Beta\Server',
            'v1' => Server::class,
        ]);

        $result = $this->artisan('jsonapi:requests', [
            'name' => 'posts',
        ]);

        $this->assertSame(1, $result);
        $this->assertNotCreated();
    }

    public function testInvalidServer(): void
    {
        config()->set('jsonapi.servers', [
            'v1' => Server::class,
        ]);

        $result = $this->artisan('jsonapi:requests', [
            'name' => 'posts',
            '--server' => 'v2',
        ]);

        $this->assertSame(1, $result);
        $this->assertNotCreated();
    }

    /**
     * @return void
     */
    private function assertAllCreated(): void
    {
        $this->assertRequestCreated();
        $this->assertQueryCreated();
        $this->assertQueryCollectionCreated();
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
        ];

        foreach ($tests as $expected) {
            $this->assertStringContainsString($expected, $content);
        }
    }

    /**
     * @return void
     */
    private function assertNotCreated(): void
    {
        $this->assertFileDoesNotExist(app_path('JsonApi/V1/Posts/PostRequest.php'));
        $this->assertFileDoesNotExist(app_path('JsonApi/V1/Posts/PostQuery.php'));
        $this->assertFileDoesNotExist(app_path('JsonApi/V1/Posts/PostCollection.php'));
    }
}
