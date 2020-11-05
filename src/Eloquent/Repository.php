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
use LaravelJsonApi\Core\Contracts\Store\QueriesAll;
use LaravelJsonApi\Core\Contracts\Store\QueriesOne;
use LaravelJsonApi\Core\Contracts\Store\QueryAllBuilder;
use LaravelJsonApi\Core\Contracts\Store\QueryOneBuilder;
use LaravelJsonApi\Core\Contracts\Store\Repository as RepositoryContract;
use LogicException;
use function is_string;

class Repository implements RepositoryContract, QueriesAll, QueriesOne
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
        $this->model = $schema->newInstance();
    }

    /**
     * Get the model for the supplied resource id.
     *
     * @param string $resourceId
     * @return Model|null
     */
    public function find(string $resourceId)
    {
        return $this
            ->query()
            ->whereResourceId($resourceId)
            ->first();
    }

    /**
     * Does a resource with the supplied id exist?
     *
     * @param string $resourceId
     * @return bool
     */
    public function exists(string $resourceId): bool
    {
        return $this
            ->query()
            ->whereResourceId($resourceId)
            ->exists();
    }

    /**
     * @return Builder
     */
    public function query(): Builder
    {
        return new Builder($this->schemas, $this->schema, $this->model->newQuery());
    }

    /**
     * @inheritDoc
     */
    public function queryAll(): QueryAllBuilder
    {
        return new QueryAll($this->query());
    }

    /**
     * @inheritDoc
     */
    public function queryOne($modelOrResourceId): QueryOneBuilder
    {
        if ($modelOrResourceId instanceof Model) {
            return new QueryOne(
                $this->schemas,
                $this->schema,
                $this->query(),
                $modelOrResourceId,
                strval($modelOrResourceId->{$this->schema->idName()})
            );
        }

        if (is_string($modelOrResourceId) && !empty($modelOrResourceId)) {
            return new QueryOne(
                $this->schemas,
                $this->schema,
                $this->query(),
                null,
                $modelOrResourceId
            );
        }

        throw new LogicException('Expecting a model or non-empty string resource id.');
    }

}
