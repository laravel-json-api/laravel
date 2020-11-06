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

namespace LaravelJsonApi\Core\Store;

use LaravelJsonApi\Contracts\Schema\Container;
use LaravelJsonApi\Contracts\Store\CreatesResources;
use LaravelJsonApi\Contracts\Store\DeletesResources;
use LaravelJsonApi\Contracts\Store\QueriesAll;
use LaravelJsonApi\Contracts\Store\QueriesOne;
use LaravelJsonApi\Contracts\Store\QueryAllBuilder;
use LaravelJsonApi\Contracts\Store\QueryOneBuilder;
use LaravelJsonApi\Contracts\Store\Repository;
use LaravelJsonApi\Contracts\Store\ResourceBuilder;
use LaravelJsonApi\Contracts\Store\Store as StoreContract;
use LaravelJsonApi\Contracts\Store\UpdatesResources;
use LaravelJsonApi\Core\Support\Str;
use LogicException;

class Store implements StoreContract
{

    /**
     * @var Container
     */
    private Container $schemas;

    /**
     * Store constructor.
     *
     * @param Container $schemas
     */
    public function __construct(Container $schemas)
    {
        $this->schemas = $schemas;
    }

    /**
     * @param string $name
     * @param mixed $arguments
     * @return Repository
     */
    public function __call(string $name, $arguments)
    {
        return $this->resources(
            Str::dasherize($name)
        );
    }

    /**
     * @inheritDoc
     */
    public function find(string $resourceType, string $resourceId)
    {
        return $this
            ->resources($resourceType)
            ->find($resourceId);
    }

    /**
     * @inheritDoc
     */
    public function exists(string $resourceType, string $resourceId): bool
    {
        return $this
            ->resources($resourceType)
            ->exists($resourceId);
    }

    /**
     * @inheritDoc
     */
    public function queryAll(string $resourceType): QueryAllBuilder
    {
        $repository = $this->resources($resourceType);

        if ($repository instanceof QueriesAll) {
            return $repository->queryAll();
        }

        throw new LogicException("Querying all {$resourceType} resources is not supported.");
    }

    /**
     * @inheritDoc
     */
    public function queryOne(string $resourceType, $modelOrResourceId): QueryOneBuilder
    {
        $repository = $this->resources($resourceType);

        if ($repository instanceof QueriesOne) {
            return $repository->queryOne($modelOrResourceId);
        }

        throw new LogicException("Querying one {$resourceType} resource is not supported.");
    }

    /**
     * @inheritDoc
     */
    public function create(string $resourceType): ResourceBuilder
    {
        $repository = $this->resources($resourceType);

        if ($repository instanceof CreatesResources) {
            return $repository->create();
        }

        throw new LogicException("Creating a {$resourceType} resource is not supported.");
    }

    /**
     * @inheritDoc
     */
    public function update(string $resourceType, $modelOrResourceId): ResourceBuilder
    {
        $repository = $this->resources($resourceType);

        if ($repository instanceof UpdatesResources) {
            return $repository->update($modelOrResourceId);
        }

        throw new LogicException("Updating a {$resourceType} resource is not supported.");
    }

    /**
     * @inheritDoc
     */
    public function delete(string $resourceType, $modelOrResourceId): void
    {
        $repository = $this->resources($resourceType);

        if ($repository instanceof DeletesResources) {
            $repository->delete($modelOrResourceId);
            return;
        }

        throw new LogicException("Deleting a {$resourceType} resource is not supported.");
    }

    /**
     * @inheritDoc
     */
    public function resources(string $resourceType): Repository
    {
        return $this->schemas
            ->schemaFor($resourceType)
            ->repository();
    }
}
