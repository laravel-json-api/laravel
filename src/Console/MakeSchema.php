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

use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;
use function array_keys;
use function array_values;
use function str_replace;

class MakeSchema extends GeneratorCommand
{

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
        $stub = parent::buildClass($name);

        $model = $this->option('model') ?: $this->guessModel();

        return $this->replaceModel($stub, $model);
    }

    /**
     * @param string $stub
     * @param string $model
     * @return string
     */
    protected function replaceModel(string $stub, string $model): string
    {
        $model = str_replace('/', '\\', $model);

        if (Str::startsWith($model, '\\')) {
            $namespacedModel = trim($model, '\\');
        } else {
            $namespacedModel = $this->qualifyModel($model);
        }

        $model = class_basename($model);
        $schema = $this->option('proxy') ? 'ProxySchema' : 'Schema';

        $replace = [
            '{{ namespacedModel }}' => $namespacedModel,
            '{{namespacedModel}}' => $namespacedModel,
            '{{ model }}' => $model,
            '{{model}}' => $model,
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
