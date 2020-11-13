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

use Generator;
use Illuminate\Contracts\Support\Arrayable;
use InvalidArgumentException;
use LaravelJsonApi\Contracts\Schema\Container as SchemaContainer;
use LaravelJsonApi\Contracts\Schema\PolymorphicRelation;
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
     * @var bool
     */
    private bool $start;

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
        $this->start = true;
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return array_values(array_unique(
            iterator_to_array($this)
        ));
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

                if ($relation instanceof PolymorphicRelation) {
                    foreach ($this->polymorph($relation) as $path) {
                        yield "{$name}.{$path}";
                    }
                    continue;
                }

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
            return $this->make(
                $this->schemas->schemaFor($relation->inverse())
            );
        }

        return null;
    }

    /**
     * Iterator over paths from a polymorphic relation.
     *
     * Iteration from polymorphs is only supported if the relation is
     * one the first level schema, i.e. is at the start of the path.
     *
     * If a polymorphic relation is later in the path, it effectively
     * terminates the iteration for that path.
     *
     * This is because in Eloquent, we only support loading polymorphic
     * relations via a morph-to map at the top-level of the include path.
     *
     * @param PolymorphicRelation $relation
     * @return Generator
     */
    private function polymorph(PolymorphicRelation $relation): Generator
    {
        if (1 < $this->depth && true === $this->start) {
            foreach ($relation->inverseTypes() as $type) {
                yield from $this->make(
                    $this->schemas->schemaFor($type)
                );
            }
        }
    }

    /**
     * @param Schema $schema
     * @return $this
     */
    private function make(Schema $schema): self
    {
        $iterator = new self($this->schemas, $schema, $this->depth - 1);
        $iterator->start = false;

        return $iterator;
    }

}
