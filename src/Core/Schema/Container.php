<?php
/**
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

namespace LaravelJsonApi\Core\Schema;

use LaravelJsonApi\Core\Contracts\Schema\Schema;
use LaravelJsonApi\Core\Contracts\Schema\Container as ContainerContract;
use function collect;

class Container implements ContainerContract
{

    /**
     * @var array
     */
    private $types;

    /**
     * @var array
     */
    private $models;

    /**
     * Container constructor.
     *
     * @param iterable $schemas
     */
    public function __construct(iterable $schemas)
    {
        $this->types = [];
        $this->models = [];

        foreach ($schemas as $schema) {
            if (!$schema instanceof Schema) {
                throw new \InvalidArgumentException('Expecting a schema.');
            }

            $schema->withContainer($this);

            $this->types[$schema->type()] = $schema;
            $this->models[$schema->model()] = $schema;
        }

        ksort($this->types);
    }

    /**
     * @inheritDoc
     */
    public function resources(): array
    {
        return collect($this->models)->map(function (Schema $schema) {
            return $schema->resource();
        })->all();
    }
}
