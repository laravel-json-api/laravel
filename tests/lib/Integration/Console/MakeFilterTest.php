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
