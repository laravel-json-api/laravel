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

use LaravelJsonApi\Laravel\Console\Concerns\ReplacesModel;
use Symfony\Component\Console\Input\InputOption;
use function array_keys;
use function array_values;
use function str_replace;

class MakeSchema extends GeneratorCommand
{
    use ReplacesModel;

    /**
     * @var string
     */
    protected $name = 'jsonapi:schema';

    /**
     * @var string
     */
    protected $description = 'Create a new JSON:API schema.';

    /**
     * @var string
     */
    protected $type = 'JSON:API schema';

    /**
     * @var string
     */
    protected $classType = 'Schema';
    
    /**
     * @inheritDoc
     */
    protected function getStub()
    {
        if ($this->option('non-eloquent')) {
            return $this->resolveStubPath('non-eloquent-schema.stub');
        }

        return $this->resolveStubPath('schema.stub');
    }

    /**
     * @inheritDoc
     */
    protected function buildClass($name)
    {
        $stub = $this->replaceSchema(parent::buildClass($name));

        $model = $this->option('model') ?: $this->guessModel();

        return $this->replaceModel($stub, $model);
    }

    /**
     * @param string $stub
     * @param string $model
     * @return string
     */
    protected function replaceSchema(string $stub): string
    {
        $schema = $this->option('proxy') ? 'ProxySchema' : 'Schema';

        $replace = [
            '{{ schema }}' => $schema,
            '{{schema}}' => $schema,
        ];

        return str_replace(
            array_keys($replace), array_values($replace), $stub
        );
    }

    /**
     * @inheritDoc
     */
    protected function getOptions()
    {
        return [
            ['force', null, InputOption::VALUE_NONE, 'Create the class even if the schema already exists'],
            ['model', 'm', InputOption::VALUE_REQUIRED, 'The model that the schema applies to.'],
            ['non-eloquent', null, InputOption::VALUE_NONE, 'Create a schema for a non-Eloquent resource.'],
            ['proxy', 'p', InputOption::VALUE_NONE, 'Create a schema for an Eloquent model proxy.'],
            ['server', 's', InputOption::VALUE_REQUIRED, 'The JSON:API server the schema exists in.'],
        ];
    }
}
