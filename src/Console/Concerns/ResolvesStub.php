<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace LaravelJsonApi\Laravel\Console\Concerns;

use function file_exists;

trait ResolvesStub
{

    /**
     * Resolve the fully-qualified path to the stub.
     *
     * @param string $stub
     * @return string
     */
    protected function resolveStubPath(string $stub): string
    {
        $customPath = $this->laravel->basePath("stubs/jsonapi/{$stub}");

        return file_exists($customPath) ? $customPath : __DIR__ . '/../../../stubs/' . $stub;
    }
}
