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
use LaravelJsonApi\Core\Contracts\Schema\SchemaAware as SchemaAwareContract;
use LaravelJsonApi\Core\Contracts\Store\Repository as RepositoryContract;
use LaravelJsonApi\Core\Schema\SchemaAware;
use LaravelJsonApi\Core\Support\Str;
use LaravelJsonApi\Eloquent\Contracts\Paginator;
use LogicException;
use function class_basename;
use function is_array;
use function ksort;
use function sprintf;

abstract class Schema implements SchemaContract, SchemaAwareContract
{

    use SchemaAware;

    /**
     * The JSON API resource type the schema corresponds to.
     *
     * @var string|null
     */
    protected ?string $type = null;

    /**
     * The model the schema corresponds to.
     *
     * @var string
     */
    protected string $model;

    /**
     * The resource the schema corresponds to.
     *
     * @var string
     */
    protected string $resource;

    /**
     * The key name for the resource id, or null to use the model's route key.
     *
     * @var string|null
     */
    protected ?string $primaryKey;

    /**
     * The relationships that should always be eager loaded.
     *
     * @var array
     */
    protected array $with = [];

    /**
     * @var Container|null
     */
    private ?Container $container;

    /**
     * @var array|null
     */
    private ?array $fields = null;

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
        return new Repository($this->schemas(), $this);
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
        if (is_array($this->fields)) {
            return $this->fields;
        }

        $this->fields = [];

        /** @var Field $field */
        foreach ($this->fields() as $field) {
            if ($field instanceof SchemaAwareContract) {
                $field->withContainer($this->schemas());
            }

            $this->fields[$field->name()] = $field;
        }

        ksort($this->fields);

        return $this->fields;
    }

}
