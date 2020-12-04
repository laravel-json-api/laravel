<?php
/*
 * Copyright 2020 Cloud Creativity Limited
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

class MakeQuery extends GeneratorCommand
{

    /**
     * @var string
     */
    protected $name = 'jsonapi:query';

    /**
     * @var string
     */
    protected $description = 'Create a new JSON:API resource query.';

    /**
     * @var string
     */
    protected $type = 'JSON:API resource query';

    /**
     * @inheritDoc
     */
    public function handle()
    {
        if ($this->option('both') && $this->option('collection')) {
            $this->input->setOption('collection', false);
        }

        $result = parent::handle();

        if (0 === $result && $this->option('both')) {
            $this->input->setOption('collection', true);
            $result = parent::handle();
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    protected function getStub()
    {
        if ($this->option('collection')) {
            return $this->resolveStubPath('query-collection.stub');
        }

        return $this->resolveStubPath('query.stub');
    }

    /**
     * @return string
     */
    protected function getClassType(): string
    {
        if ($this->option('collection')) {
            return 'CollectionQuery';
        }

        return 'Query';
    }

    /**
     * @inheritDoc
     */
    protected function getOptions()
    {
        return [
            ['collection', 'c', InputOption::VALUE_NONE, 'Create a query collection class.'],
            ['both', 'b', InputOption::VALUE_NONE, 'Create a query and a query collection class.'],
            ['force', null, InputOption::VALUE_NONE, 'Create the class even if the query already exists'],
            ['server', 's', InputOption::VALUE_NONE, 'The JSON:API server the query exists in.'],
        ];
    }

}
