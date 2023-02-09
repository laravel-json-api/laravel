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
