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

class MakeControllerTest extends TestCase
{

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->markTestSkipped('@TODO requires bugfix: https://github.com/laravel/framework/pull/45864');
        parent::setUp();
        $this->withoutMockingConsoleOutput();

        $files = new Filesystem();
        $files->deleteDirectory(app_path('Http/Controllers/Api'));
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $files = new Filesystem();
        $files->deleteDirectory(app_path('Http/Controllers/Api'));
    }

    public function test(): void
    {
        $result = $this->artisan('jsonapi:controller', [
            'name' => 'Api/V1/PostController'
        ]);

        $this->assertSame(0, $result);
        $this->assertControllerCreated();
    }

    /**
     * @return void
     */
    private function assertControllerCreated(): void
    {
        $this->assertFileExists($path = app_path('Http/Controllers/Api/V1/PostController.php'));
        $content = file_get_contents($path);
        $this->assertStringContainsString('namespace App\Http\Controllers\Api\V1;', $content);
        $this->assertStringContainsString('use App\Http\Controllers\Controller;', $content);
        $this->assertStringContainsString('class PostController extends Controller', $content);
    }

}
