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
use LaravelJsonApi\Contracts\Store\CreatesResources;
use LaravelJsonApi\Contracts\Store\DeletesResources;
use LaravelJsonApi\Contracts\Store\QueriesAll;
use LaravelJsonApi\Contracts\Store\QueriesOne;
use LaravelJsonApi\Contracts\Store\QueryAllBuilder;
use LaravelJsonApi\Contracts\Store\QueryOneBuilder;
use LaravelJsonApi\Contracts\Store\Repository as RepositoryContract;
use LaravelJsonApi\Contracts\Store\ResourceBuilder;
use LaravelJsonApi\Contracts\Store\UpdatesResources;
use LogicException;
use RuntimeException;
use function is_string;

class Repository implements
    RepositoryContract,
    QueriesAll,
    QueriesOne,
    CreatesResources,
    UpdatesResources,
    DeletesResources
{

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
     * @param Schema $schema
     */
    public function __construct(Schema $schema)
    {
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
        return new Builder($this->schema, $this->model->newQuery());
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
                $this->schema,
                $this->query(),
                $modelOrResourceId,
                strval($modelOrResourceId->{$this->schema->idName()})
            );
        }

        if (is_string($modelOrResourceId) && !empty($modelOrResourceId)) {
            return new QueryOne(
                $this->schema,
                $this->query(),
                null,
                $modelOrResourceId
            );
        }

        throw new LogicException('Expecting a model or non-empty string resource id.');
    }

    /**
     * @inheritDoc
     */
    public function create(): ResourceBuilder
    {
        return new Hydrator(
            $this->schema,
            $this->schema->newInstance()
        );
    }

    /**
     * @inheritDoc
     */
    public function update($modelOrResourceId): ResourceBuilder
    {
        return new Hydrator(
            $this->schema,
            $this->findOrFail($modelOrResourceId)
        );
    }

    /**
     * @inheritDoc
     */
    public function delete($modelOrResourceId): void
    {
        $model = $this->findOrFail($modelOrResourceId);

        if (true !== $model->getConnection()->transaction(fn() => $model->forceDelete())) {
            throw new RuntimeException('Failed to delete resource.');
        }
    }

    /**
     * @param Model|string $modelOrResourceId
     * @return Model
     */
    private function findOrFail($modelOrResourceId): Model
    {
        if ($modelOrResourceId instanceof $this->model) {
            return $modelOrResourceId;
        }

        if (is_string($modelOrResourceId)) {
            return $this
                ->query()
                ->whereResourceId($modelOrResourceId)
                ->firstOrFail();
        }

        throw new LogicException(sprintf(
            'Expecting a %s instance or a string resource id.',
            get_class($this->model)
        ));
    }

}
