<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace LaravelJsonApi\Laravel\Console;

use Illuminate\Console\GeneratorCommand as BaseGeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

class MakeSortField extends BaseGeneratorCommand
{

    use Concerns\ResolvesStub;

    /**
     * @var string
     */
    protected $name = 'jsonapi:sort-field';

    /**
     * @var string
     */
    protected $description = 'Create a new JSON:API sort field.';

    /**
     * @var string
     */
    protected $type = 'JSON:API sort field';

    /**
     * @inheritDoc
     */
    protected function getStub()
    {
        return $this->resolveStubPath('sort-field.stub');
    }

    /**
     * @inheritDoc
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        $jsonApi = trim(config('jsonapi.namespace') ?: 'JsonApi', '\\');

        return $rootNamespace . '\\' . $jsonApi . '\\' . 'Sorting';
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['force', null, InputOption::VALUE_NONE, 'Create the class even if the sort field already exists'],
        ];
    }

}
