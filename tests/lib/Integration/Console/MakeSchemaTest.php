<?php
/*
 * Copyright 2022 Cloud Creativity Limited
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

class MakeSchemaTest extends TestCase
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

        $result = $this->artisan('jsonapi:schema posts');

        $this->assertSame(0, $result);
        $this->assertSchemaCreated('App\Models\Post', 'Post');
    }

    public function testModelWithoutNamespace(): void
    {
        config()->set('jsonapi.servers', [
            'v1' => Server::class,
        ]);

        $result = $this->artisan('jsonapi:schema posts --model BlogPost');

        $this->assertSame(0, $result);
        $this->assertSchemaCreated('App\Models\BlogPost', 'BlogPost');
    }

    public function testModelWithNamespace(): void
    {
        config()->set('jsonapi.servers', [
            'v1' => Server::class,
        ]);

        $result = $this->artisan('jsonapi:schema', [
            'name' => 'posts',
            '--model' => '\App\BlogPost',
        ]);

        $this->assertSame(0, $result);
        $this->assertSchemaCreated('App\BlogPost', 'BlogPost');
    }

    public function testServer(): void
    {
        config()->set('jsonapi.servers', [
            'beta' => 'App\JsonApi\Beta\Server',
            'v1' => Server::class,
        ]);

        $result = $this->artisan('jsonapi:schema posts --server v1');

        $this->assertSame(0, $result);
        $this->assertSchemaCreated('App\Models\Post', 'Post');
    }

    public function testProxy(): void
    {
        config()->set('jsonapi.servers', [
            'v1' => Server::class,
        ]);

        $result = $this->artisan('jsonapi:schema', [
            'name' => 'user-accounts',
            '--proxy' => true,
            '--model' => '\App\JsonApi\Proxies\UserAccount',
        ]);

        $this->assertSame(0, $result);
        $this->assertProxySchemaCreated('App\JsonApi\Proxies\UserAccount', 'UserAccount');
    }

    public function testNonEloquent(): void
    {
        config()->set('jsonapi.servers', [
            'v1' => Server::class,
        ]);

        $result = $this->artisan('jsonapi:schema', [
            'name' => 'sites',
            '--model' => '\App\Entities\Site',
            '--non-eloquent' => true,
        ]);

        $this->assertSame(0, $result);
        $this->assertNonEloquentSchemaCreated('App\Entities\Site', 'Site');
    }

    public function testNoServer(): void
    {
        config()->set('jsonapi.servers', [
            'beta' => 'App\JsonApi\Beta\Server',
            'v1' => Server::class,
        ]);

        $result = $this->artisan('jsonapi:schema', [
            'name' => 'posts',
        ]);

        $this->assertSame(1, $result);
        $this->assertSchemaNotCreated();
    }

    public function testInvalidServer(): void
    {
        config()->set('jsonapi.servers', [
            'v1' => Server::class,
        ]);

        $result = $this->artisan('jsonapi:schema', [
            'name' => 'posts',
            '--server' => 'v2',
        ]);

        $this->assertSame(1, $result);
        $this->assertSchemaNotCreated();
    }

    /**
     * @param string $namespacedModel
     * @param string $model
     * @return void
     */
    private function assertSchemaCreated(string $namespacedModel, string $model): void
    {
        $this->assertFileExists($path = app_path('JsonApi/V1/Posts/PostSchema.php'));
        $content = file_get_contents($path);

        $tests = [
            'namespace App\JsonApi\V1\Posts;',
            'use LaravelJsonApi\Eloquent\Schema;',
            'class PostSchema extends Schema',
            "use {$namespacedModel};",
            "public static string \$model = {$model}::class;",
        ];

        foreach ($tests as $expected) {
            $this->assertStringContainsString($expected, $content);
        }

        $missing = [
            'LaravelJsonApi\Eloquent\ProxySchema',
            'LaravelJsonApi\Core\Schema\Schema',
        ];

        foreach ($missing as $notExpected) {
            $this->assertStringNotContainsString($notExpected, $content);
        }
    }

    /**
     * @param string $namespacedModel
     * @param string $model
     * @return void
     */
    private function assertProxySchemaCreated(string $namespacedModel, string $model): void
    {
        $this->assertFileExists($path = app_path('JsonApi/V1/UserAccounts/UserAccountSchema.php'));
        $content = file_get_contents($path);

        $tests = [
            'namespace App\JsonApi\V1\UserAccounts;',
            'use LaravelJsonApi\Eloquent\ProxySchema;',
            'class UserAccountSchema extends ProxySchema',
            "use {$namespacedModel};",
            "public static string \$model = {$model}::class;",
        ];

        foreach ($tests as $expected) {
            $this->assertStringContainsString($expected, $content);
        }

        $missing = [
            'LaravelJsonApi\Eloquent\Schema',
            'LaravelJsonApi\Core\Schema\Schema',
        ];

        foreach ($missing as $notExpected) {
            $this->assertStringNotContainsString($notExpected, $content);
        }
    }

    /**
     * @param string $namespacedModel
     * @param string $model
     * @return void
     */
    private function assertNonEloquentSchemaCreated(string $namespacedModel, string $model): void
    {
        $this->assertFileExists($path = app_path('JsonApi/V1/Sites/SiteSchema.php'));
        $content = file_get_contents($path);

        $tests = [
            'namespace App\JsonApi\V1\Sites;',
            'use LaravelJsonApi\Core\Schema\Schema;',
            'class SiteSchema extends Schema',
            "use {$namespacedModel};",
            "public static string \$model = {$model}::class;",
        ];

        foreach ($tests as $expected) {
            $this->assertStringContainsString($expected, $content);
        }

        $missing = [
            'LaravelJsonApi\Eloquent',
        ];

        foreach ($missing as $notExpected) {
            $this->assertStringNotContainsString($notExpected, $content);
        }
    }

    /**
     * @return void
     */
    private function assertSchemaNotCreated(): void
    {
        $this->assertFileDoesNotExist(app_path('JsonApi/V1/Posts/PostSchema.php'));
    }
}
