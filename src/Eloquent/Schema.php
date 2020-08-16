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

namespace LaravelJsonApi\Eloquent;

use LaravelJsonApi\Core\Contracts\Schema\Attribute;
use LaravelJsonApi\Core\Contracts\Schema\Container;
use LaravelJsonApi\Core\Contracts\Schema\Field;
use LaravelJsonApi\Core\Contracts\Schema\Relation;
use LaravelJsonApi\Core\Contracts\Schema\Schema as SchemaContract;
use LaravelJsonApi\Core\Contracts\Store\Repository as RepositoryContract;
use LaravelJsonApi\Core\Support\Str;
use LaravelJsonApi\Eloquent\Contracts\Paginator;
use LogicException;
use function class_basename;
use function collect;
use function sprintf;

abstract class Schema implements SchemaContract
{

    /**
     * The JSON API resource type the schema corresponds to.
     *
     * @var string|null
     */
    protected $type;

    /**
     * The model the schema corresponds to.
     *
     * @var string|null
     */
    protected $model;

    /**
     * The resource the schema corresponds to.
     *
     * @var string|null
     */
    protected $resource;

    /**
     * The key name for the resource id, or null to use the model's route key.
     *
     * @var string|null
     */
    protected $primaryKey;

    /**
     * The relationships that should always be eager loaded.
     *
     * @var array
     */
    protected $with = [];

    /**
     * @var Container|null
     */
    private $container;

    /**
     * @var array
     */
    private $fields;

    /**
     * Get the resource attributes.
     *
     * @return array
     */
    abstract public function fields(): array;

    /**
     * Get the filters for the resource.
     *
     * @return array
     */
    abstract public function filters(): array;

    /**
     * Get the paginator to use when fetching collections of this resource.
     *
     * @return Paginator|null
     */
    abstract public function pagination(): ?Paginator;

    /**
     * @inheritDoc
     */
    public function type(): string
    {
        if ($this->type) {
            return $this->type;
        }

        return $this->type = $this->guessType();
    }

    /**
     * @inheritDoc
     */
    public function model(): string
    {
        if ($this->model) {
            return $this->model;
        }

        throw new LogicException('The model class name must be set.');
    }

    /**
     * @inheritDoc
     */
    public function resource(): string
    {
        if ($this->resource) {
            return $this->resource;
        }

        throw new LogicException('The resource class name must be set.');
    }

    /**
     * @inheritDoc
     */
    public function repository(): RepositoryContract
    {
        return new Repository($this);
    }

    /**
     * @return string
     */
    public function idName(): ?string
    {
        return $this->primaryKey;
    }

    /**
     * @return iterable
     */
    public function attributes(): iterable
    {
        foreach ($this->allFields() as $name => $field) {
            if ($field instanceof Attribute) {
                yield $name => $field;
            }
        }
    }

    /**
     * @param string $name
     * @return Attribute
     */
    public function attribute(string $name): Attribute
    {
        $this->fields ?: $this->allFields();
        $field = $this->fields[$name] ?? null;

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
     * @return iterable
     */
    public function relationships(): iterable
    {
        foreach ($this->allFields() as $name => $field) {
            if ($field instanceof Relation) {
                yield $name => $field;
            }
        }
    }

    /**
     * @param string $name
     * @return Relation
     */
    public function relationship(string $name): Relation
    {
        $this->fields ?: $this->allFields();
        $field = $this->fields[$name] ?? null;

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
    public function withContainer(Container $container): void
    {
        if ($this->container) {
            throw new LogicException('Not expecting schema container to be changed.');
        }

        $this->container = $container;
    }

    /**
     * @return Container
     */
    protected function schemas(): Container
    {
        if ($this->container) {
            return $this->container;
        }

        throw new LogicException('Expecting schemas to have access to their schema container.');
    }


    /**
     * @return string
     */
    private function guessType(): string
    {
        $type = Str::replaceLast('Schema', '', class_basename($this));

        return Str::plural(Str::dasherize($type));
    }

    /**
     * @return array
     */
    private function allFields(): array
    {
        if ($this->fields) {
            return $this->fields;
        }

        return $this->fields = collect($this->fields())->keyBy(function (Field $field) {
            return $field->name();
        })->sortKeys()->all();
    }

}
