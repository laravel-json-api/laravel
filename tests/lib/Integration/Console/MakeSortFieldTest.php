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

class MakeSortFieldTest extends TestCase
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

        $result = $this->artisan('jsonapi:sort-field CustomSort');

        $this->assertSame(0, $result);
        $this->assertSortFieldCreated('JsonApi');
    }

    public function testCustomNamespace(): void
    {
        config()->set('jsonapi', [
            'namespace' => 'Foo\Bar',
        ]);

        $result = $this->artisan('jsonapi:sort-field', [
            'name' => 'CustomSort'
        ]);

        $this->assertSame(0, $result);
        $this->assertSortFieldCreated('Foo\Bar');
    }

    /**
     * @param string $namespace
     * @return void
     */
    private function assertSortFieldCreated(string $namespace): void
    {
        $path = str_replace('\\', '/', $namespace);

        $this->assertFileExists($path = app_path("{$path}/Sorting/CustomSort.php"));
        $content = file_get_contents($path);

        $tests = [
            "namespace App\\{$namespace}\\Sorting;",
            'use LaravelJsonApi\Eloquent\Contracts\SortField;',
            'class CustomSort implements SortField',
            '* @return CustomSort',
            '* CustomSort constructor.',
        ];

        foreach ($tests as $expected) {
            $this->assertStringContainsString($expected, $content);
        }
    }

}
