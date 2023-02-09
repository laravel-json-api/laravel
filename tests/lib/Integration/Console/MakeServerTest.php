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

class MakeServerTest extends TestCase
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
        $files->deleteDirectory(app_path('Foo'));
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $files = new Filesystem();
        $files->deleteDirectory(app_path('JsonApi'));
        $files->deleteDirectory(app_path('Foo'));
    }

    public function test(): void
    {
        config()->set('jsonapi', require __DIR__ . '/../../../../config/jsonapi.php');

        $result = $this->artisan('jsonapi:server v1');

        $this->assertSame(0, $result);
        $this->assertServerCreated('JsonApi', 'V1', '/api/v1');
    }

    public function testWithUri(): void
    {
        config()->set('jsonapi', [
            'namespace' => 'JsonApi',
            'servers' => [
                'v1' => Server::class,
            ],
        ]);

        $result = $this->artisan('jsonapi:server v2 --uri "http://example.com/foo/bar/"');

        $this->assertSame(0, $result);
        $this->assertServerCreated('JsonApi', 'V2', 'http://example.com/foo/bar');
    }

    public function testCustomNamespace(): void
    {
        config()->set('jsonapi', [
            'namespace' => 'Foo\Bar',
            'servers' => [
                'v1' => Server::class,
            ],
        ]);

        $result = $this->artisan('jsonapi:server', [
            'name' => 'v2'
        ]);

        $this->assertSame(0, $result);
        $this->assertServerCreated('Foo\Bar', 'V2', '/api/v2');
    }

    public function testServerExists(): void
    {
        config()->set('jsonapi', [
            'namespace' => 'JsonApi',
            'servers' => [
                'v1' => Server::class,
            ],
        ]);

        $result = $this->artisan('jsonapi:server', [
            'name' => 'v1'
        ]);

        $this->assertSame(1, $result);
        $this->assertServerNotCreated('JsonApi', 'V1');
    }

    /**
     * @param string $namespace
     * @param string $name
     * @param string $uri
     * @return void
     */
    private function assertServerCreated(string $namespace, string $name, string $uri): void
    {
        $path = str_replace('\\', '/', $namespace);

        $this->assertFileExists($path = app_path("{$path}/{$name}/Server.php"));
        $content = file_get_contents($path);

        $tests = [
            "namespace App\\{$namespace}\\{$name};",
            'use LaravelJsonApi\Core\Server\Server as BaseServer;',
            'class Server extends BaseServer',
            "protected string \$baseUri = '{$uri}';"
        ];

        foreach ($tests as $expected) {
            $this->assertStringContainsString($expected, $content);
        }
    }

    /**
     * @param string $namespace
     * @param string $name
     * @return void
     */
    private function assertServerNotCreated(string $namespace, string $name): void
    {
        $this->assertFileDoesNotExist(app_path("{$namespace}/{$name}/Server.php"));
    }

}
