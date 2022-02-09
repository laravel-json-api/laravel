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

namespace LaravelJsonApi\Laravel\Console;

use Illuminate\Console\GeneratorCommand as BaseGeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

class MakeController extends BaseGeneratorCommand
{

    use Concerns\ResolvesStub;

    /**
     * @var string
     */
    protected $name = 'jsonapi:controller';

    /**
     * @var string
     */
    protected $description = 'Create a new JSON:API controller.';

    /**
     * @var string
     */
    protected $type = 'JSON:API controller';

    /**
     * @inheritDoc
     */
    protected function getStub()
    {
        return $this->resolveStubPath('controller.stub');
    }

    /**
     * @inheritDoc
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Http\Controllers';
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['force', null, InputOption::VALUE_NONE, 'Create the class even if the controller already exists'],
        ];
    }
}
