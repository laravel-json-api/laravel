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

use LaravelJsonApi\Contracts\Schema\Attribute;
use LaravelJsonApi\Contracts\Schema\Field;
use LaravelJsonApi\Contracts\Schema\Relation;
use LaravelJsonApi\Contracts\Schema\Schema as SchemaContract;
use LaravelJsonApi\Contracts\Schema\SchemaAware as SchemaAwareContract;
use LogicException;
use function sprintf;

abstract class Schema implements SchemaContract, SchemaAwareContract, \IteratorAggregate
{

    use SchemaAware;

    /**
     * The maximum depth of include paths.
     *
     * @var int
     */
    protected int $maxDepth = 1;

    /**
     * @var array|null
     */
    private ?array $fields = null;

    /**
     * Get the resource fields.
     *
     * @return iterable
     */
    abstract public function fields(): iterable;

    /**
     * @inheritDoc
     */
    public function getIterator()
    {
        yield from $this->allFields();
    }

    /**
     * @inheritDoc
     */
    public function attributes(): iterable
    {
        foreach ($this as $field) {
            if ($field instanceof Attribute) {
                yield $field->name() => $field;
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function attribute(string $name): Attribute
    {
        $field = $this->allFields()[$name] ?? null;

        if ($field instanceof Attribute) {
            return $field;
        }

        throw new LogicException(sprintf(
            'Attribute %s does not exist on resource schema %s.',
            $name,
            $this->type()
        ));
    }

    /**
     * @inheritDoc
     */
    public function relationships(): iterable
    {
        foreach ($this as $field) {
            if ($field instanceof Relation) {
                yield $field->name() => $field;
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function relationship(string $name): Relation
    {
        $field = $this->allFields()[$name] ?? null;

        if ($field instanceof Relation) {
            return $field;
        }

        throw new LogicException(sprintf(
            'Relationship %s does not exist on resource schema %s.',
            $name,
            $this->type()
        ));
    }

    /**
     * @inheritDoc
     */
    public function includePaths(): iterable
    {
        return new IncludePathIterator(
            $this->schemas(),
            $this,
            $this->maxDepth
        );
    }

    /**
     * @inheritDoc
     */
    public function sparseFields(): iterable
    {
        /** @var Field $field */
        foreach ($this as $field) {
            if ($field->isSparseField()) {
                yield $field->name();
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function sortable(): iterable
    {
        /** @var Attribute $attr */
        foreach ($this->attributes() as $attr) {
            if ($attr->isSortable()) {
                yield $attr->name();
            }
        }
    }

    /**
     * @return array
     */
    private function allFields(): array
    {
        if (is_array($this->fields)) {
            return $this->fields;
        }

        return $this->fields = collect($this->fields())->keyBy(function (Field $field) {
            if ($field instanceof SchemaAwareContract) {
                $field->withSchemas($this->schemas());
            }

            return $field->name();
        })->sortKeys()->all();
    }
}
