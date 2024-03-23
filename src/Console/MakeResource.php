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

class MakeResource extends GeneratorCommand
{
    use ReplacesModel;

    /**
     * @var string
     */
    protected $name = 'jsonapi:resource';

    /**
     * @var string
     */
    protected $description = 'Create a new JSON:API resource.';

    /**
     * @var string
     */
    protected $type = 'JSON:API resource';

    /**
     * @var string
     */
    protected $classType = 'Resource';

    /**
     * @inheritDoc
     */
    protected function getStub()
    {
        return $this->resolveStubPath('resource.stub');
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
     * @inheritDoc
     */
    protected function getOptions()
    {
        return [
            ['force', null, InputOption::VALUE_NONE, 'Create the class even if the resource already exists'],
            ['model', 'm', InputOption::VALUE_REQUIRED, 'The model that the resource applies to.'],
            ['server', 's', InputOption::VALUE_REQUIRED, 'The JSON:API server the resource exists in.'],
        ];
    }
}
