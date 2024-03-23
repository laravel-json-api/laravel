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
