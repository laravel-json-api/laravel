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

class MakeRequest extends GeneratorCommand
{

    /**
     * @var string
     */
    protected $name = 'jsonapi:request';

    /**
     * @var string
     */
    protected $description = 'Create a new JSON:API resource request.';

    /**
     * @var string
     */
    protected $type = 'JSON:API resource request';

    /**
     * @var string
     */
    protected $classType = 'Request';

    /**
     * @inheritDoc
     */
    protected function getStub()
    {
        return $this->resolveStubPath('request.stub');
    }

    /**
     * @inheritDoc
     */
    protected function getOptions()
    {
        return [
            ['force', null, InputOption::VALUE_NONE, 'Create the class even if the request already exists'],
            ['server', 's', InputOption::VALUE_REQUIRED, 'The JSON:API server the request exists in.'],
        ];
    }

}
