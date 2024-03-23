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

class MakeControllerTest extends TestCase
{

    /**
     * @return void
     */
    protected function setUp(): void
    {
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
