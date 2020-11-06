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

use Illuminate\Contracts\Support\Arrayable;
use InvalidArgumentException;
use LaravelJsonApi\Contracts\Schema\Container as SchemaContainer;
use LaravelJsonApi\Contracts\Schema\Relation;
use LaravelJsonApi\Contracts\Schema\Schema;

class IncludePathIterator implements \IteratorAggregate, Arrayable
{

    /**
     * @var SchemaContainer
     */
    private SchemaContainer $schemas;

    /**
     * @var Schema
     */
    private Schema $schema;

    /**
     * @var int
     */
    private int $depth;

    /**
     * IncludePathIterator constructor.
     *
     * @param SchemaContainer $schemas
     * @param Schema $schema
     * @param int $depth
     */
    public function __construct(SchemaContainer $schemas, Schema $schema, int $depth)
    {
        if (1 > $depth) {
            throw new InvalidArgumentException('Expecting depth to be one or greater.');
        }

        $this->schemas = $schemas;
        $this->schema = $schema;
        $this->depth = $depth;
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return iterator_to_array($this);
    }

    /**
     * @inheritDoc
     */
    public function getIterator()
    {
        /** @var Relation $relation */
        foreach ($this->schema->relationships() as $relation) {
            if ($relation->isIncludePath()) {
                yield $name = $relation->name();

                if ($next = $this->next($relation)) {
                    foreach ($next as $path) {
                        yield "{$name}.{$path}";
                    }
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return $this->all();
    }

    /**
     * @param Relation $relation
     * @return IncludePathIterator|null
     */
    private function next(Relation $relation): ?self
    {
        if (1 < $this->depth) {
            return new self(
                $this->schemas,
                $this->schemas->schemaFor($relation->inverse()),
                $this->depth - 1
            );
        }

        return null;
    }

}
