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

use Illuminate\Database\Eloquent\Model;
use LaravelJsonApi\Core\Contracts\Schema\Container;
use LaravelJsonApi\Core\Contracts\Schema\Schema as SchemaContract;
use LaravelJsonApi\Core\Support\Str;
use LaravelJsonApi\Eloquent\Contracts\Paginator;
use LogicException;
use function class_basename;

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
     * @return string
     */
    public function idName(): string
    {
        if ($this->primaryKey) {
            return $this->primaryKey;
        }

        return $this->primaryKey = $this->newInstance()->getRouteKeyName();
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
     * @return Model
     */
    protected function newInstance(): Model
    {
        $class = $this->model();

        return new $class;
    }

    /**
     * @return string
     */
    private function guessType(): string
    {
        $type = Str::replaceLast('Schema', '', class_basename($this));

        return Str::plural(Str::dasherize($type));
    }

}
