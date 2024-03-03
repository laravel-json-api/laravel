<?php
/*
 * Copyright 2024 Cloud Creativity Limited
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

class MakeResourceTest extends TestCase
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

        $result = $this->artisan('jsonapi:resource posts');

        $this->assertSame(0, $result);
        $this->assertResourceCreated();
    }

    public function testModelWithoutNamespace(): void
    {
        config()->set('jsonapi.servers', [
            'v1' => Server::class,
        ]);

        $result = $this->artisan('jsonapi:resource posts --model BlogPost');

        $this->assertSame(0, $result);
        $this->assertResourceCreated('App\Models\BlogPost', 'BlogPost');
    }

    public function testModelWithNamespace(): void
    {
        config()->set('jsonapi.servers', [
            'v1' => Server::class,
        ]);

        $result = $this->artisan('jsonapi:resource', [
            'name' => 'posts',
            '--model' => '\App\Foo\Bar\BlogPost',
        ]);

        $this->assertSame(0, $result);
        $this->assertResourceCreated('App\Foo\Bar\BlogPost', 'BlogPost');
    }

    public function testServer(): void
    {
        config()->set('jsonapi.servers', [
            'beta' => 'App\JsonApi\Beta\Server',
            'v1' => Server::class,
        ]);

        $result = $this->artisan('jsonapi:resource posts --server v1');

        $this->assertSame(0, $result);
        $this->assertResourceCreated();
    }

    public function testNoServer(): void
    {
        config()->set('jsonapi.servers', [
            'beta' => 'App\JsonApi\Beta\Server',
            'v1' => Server::class,
        ]);

        $result = $this->artisan('jsonapi:resource', [
            'name' => 'posts',
        ]);

        $this->assertSame(1, $result);
        $this->assertResourceNotCreated();
    }

    public function testInvalidServer(): void
    {
        config()->set('jsonapi.servers', [
            'v1' => Server::class,
        ]);

        $result = $this->artisan('jsonapi:resource', [
            'name' => 'posts',
            '--server' => 'v2',
        ]);

        $this->assertSame(1, $result);
        $this->assertResourceNotCreated();
    }

    /**
     * @param string $namespacedModel
     * @param string $model
     * @return void
     */
    private function assertResourceCreated(
        string $namespacedModel = 'App\Models\Post',
        string $model = 'Post'
    ): void
    {
        $this->assertFileExists($path = app_path('JsonApi/V1/Posts/PostResource.php'));
        $content = file_get_contents($path);

        $tests = [
            'namespace App\JsonApi\V1\Posts;',
            'use LaravelJsonApi\Core\Resources\JsonApiResource;',
            'class PostResource extends JsonApiResource',
            "use {$namespacedModel};",
            "@property {$model} \$resource",
        ];

        foreach ($tests as $expected) {
            $this->assertStringContainsString($expected, $content);
        }
    }

    /**
     * @return void
     */
    private function assertResourceNotCreated(): void
    {
        $this->assertFileDoesNotExist(app_path('JsonApi/V1/Posts/PostResource.php'));
    }
}
