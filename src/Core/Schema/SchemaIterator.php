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

use Illuminate\Contracts\Container\Container as IlluminateContainer;
use LaravelJsonApi\Core\Contracts\Schema\Schema;
use RuntimeException;
use Throwable;

class SchemaIterator implements \IteratorAggregate
{

    /**
     * @var IlluminateContainer
     */
    private $container;

    /**
     * @var iterable
     */
    private $schemas;

    /**
     * SchemaIterator constructor.
     *
     * @param IlluminateContainer $container
     * @param iterable $schemas
     */
    public function __construct(IlluminateContainer $container, iterable $schemas)
    {
        $this->container = $container;
        $this->schemas = $schemas;
    }

    /**
     * @inheritDoc
     */
    public function getIterator()
    {
        foreach ($this->schemas as $schemaClass) {
            yield $this->make($schemaClass);
        }
    }

    /**
     * @param string $schemaClass
     * @return Schema
     */
    private function make(string $schemaClass): Schema
    {
        try {
            $schema = $this->container->make($schemaClass);
        } catch (Throwable $ex) {
            throw new RuntimeException("Unable to create schema {$schemaClass}.", 0, $ex);
        }

        if ($schema instanceof Schema) {
            return $schema;
        }

        throw new RuntimeException("Class {$schema} is not a JSON API schema.");
    }

}
