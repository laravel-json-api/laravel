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

use Illuminate\Filesystem\Filesystem;
use LaravelJsonApi\Laravel\Tests\Integration\TestCase;

class MakeFilterTest extends TestCase
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
        config()->set('jsonapi', [
            'namespace' => 'JsonApi',
        ]);

        $result = $this->artisan('jsonapi:filter CustomFilter');

        $this->assertSame(0, $result);
        $this->assertFilterCreated('JsonApi');
    }

    public function testCustomNamespace(): void
    {
        config()->set('jsonapi', [
            'namespace' => 'Foo\Bar',
        ]);

        $result = $this->artisan('jsonapi:filter', [
            'name' => 'CustomFilter'
        ]);

        $this->assertSame(0, $result);
        $this->assertFilterCreated('Foo\Bar');
    }

    /**
     * @param string $namespace
     * @return void
     */
    private function assertFilterCreated(string $namespace): void
    {
        $path = str_replace('\\', '/', $namespace);

        $this->assertFileExists($path = app_path("{$path}/Filters/CustomFilter.php"));
        $content = file_get_contents($path);

        $tests = [
            "namespace App\\{$namespace}\\Filters;",
            'use LaravelJsonApi\Eloquent\Contracts\Filter;',
            'class CustomFilter implements Filter',
            '* @return CustomFilter',
            '* CustomFilter constructor.',
        ];

        foreach ($tests as $expected) {
            $this->assertStringContainsString($expected, $content);
        }
    }

}
