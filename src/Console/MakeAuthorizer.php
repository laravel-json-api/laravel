<?php
/*
 * Copyright 2021 Cloud Creativity Limited
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

use Symfony\Component\Console\Input\InputOption;

class MakeAuthorizer extends GeneratorCommand
{

    /**
     * @var string
     */
    protected $name = 'jsonapi:authorizer';

    /**
     * @var string
     */
    protected $description = 'Create a new JSON:API authorizer.';

    /**
     * @var string
     */
    protected $type = 'JSON:API authorizer';

    /**
     * @var string
     */
    protected $classType = 'Authorizer';

    /**
     * @inheritDoc
     */
    protected function getStub()
    {
        return $this->resolveStubPath('authorizer.stub');
    }

    /**
     * @param string $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        if ($this->option('resource')) {
            return parent::getDefaultNamespace($rootNamespace);
        }

        $jsonApi = trim(config('jsonapi.namespace') ?: 'JsonApi', '\\');

        return $rootNamespace . '\\' . $jsonApi . '\\' . 'Authorizers';
    }

    /**
     * @inheritDoc
     */
    protected function doesRequireServer(): bool
    {
        return true === $this->option('resource');
    }

    /**
     * @inheritDoc
     */
    protected function getOptions()
    {
        return [
            ['force', null, InputOption::VALUE_NONE, 'Create the class even if the schema already exists.'],
            ['resource', 'r', InputOption::VALUE_NONE, 'Whether the authorizer is for a specific resource type.'],
            ['server', 's', InputOption::VALUE_REQUIRED, 'The JSON:API server the schema exists in.'],
        ];
    }

}
