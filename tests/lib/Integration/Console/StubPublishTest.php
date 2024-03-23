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

class StubPublishTest extends TestCase
{

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMockingConsoleOutput();

        $files = new Filesystem();
        $files->deleteDirectory(base_path('stubs'));
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $files = new Filesystem();
        $files->deleteDirectory(base_path('stubs'));
    }

    public function test(): void
    {
        $result = $this->artisan('jsonapi:stubs');

        $this->assertSame(0, $result);

        $files = [
            'controller.stub',
            'filter.stub',
            'query.stub',
            'query-collection.stub',
            'request.stub',
            'resource.stub',
            'schema.stub',
            'server.stub',
        ];

        foreach ($files as $file) {
            $expected = __DIR__ . '/../../../../stubs/' . $file;
            $actual = base_path('stubs/jsonapi/' . $file);
            $this->assertFileEquals($expected, $actual);
        }
    }

}
