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
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use LaravelJsonApi\Core\Contracts\Schema\Container;
use LaravelJsonApi\Core\Contracts\Store\QueriesAll;
use LaravelJsonApi\Core\Contracts\Store\QueryBuilder;
use LaravelJsonApi\Core\Contracts\Store\Repository as RepositoryContract;

class Repository implements RepositoryContract, QueriesAll
{

    /**
     * @var Container
     */
    private Container $schemas;

    /**
     * @var Schema
     */
    private Schema $schema;

    /**
     * @var Model
     */
    private Model $model;

    /**
     * Repository constructor.
     *
     * @param Container $schemas
     * @param Schema $schema
     */
    public function __construct(Container $schemas, Schema $schema)
    {
        $this->schemas = $schemas;
        $this->schema = $schema;
        $this->model = $this->newInstance();
    }

    /**
     * Get the model for the supplied resource id.
     *
     * @param string $resourceId
     * @return Model|null
     */
    public function find(string $resourceId)
    {
        return $this->newQuery()->where(
            $this->idName(),
            $resourceId
        )->first();
    }

    /**
     * Does a resource with the supplied id exist?
     *
     * @param string $resourceId
     * @return bool
     */
    public function exists(string $resourceId): bool
    {
        return $this->newQuery()->where(
            $this->idName(),
            $resourceId
        )->exists();
    }

    /**
     * @inheritDoc
     */
    public function query(): QueryBuilder
    {
        return new Builder($this->schemas, $this->schema, $this->newQuery());
    }

    /**
     * @return string
     */
    private function idName(): string
    {
        if ($id = $this->schema->idName()) {
            return $id;
        }

        return $this->model->getRouteKeyName();
    }

    /**
     * @return Model
     */
    private function newInstance(): Model
    {
        $class = $this->schema->model();

        return new $class;
    }

    /**
     * @return EloquentBuilder
     */
    private function newQuery(): EloquentBuilder
    {
        return $this->model->newQuery();
    }
}
