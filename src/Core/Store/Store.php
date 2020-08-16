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

use Illuminate\Database\Eloquent\Model;
use LaravelJsonApi\Core\Contracts\Schema\Container;
use LaravelJsonApi\Core\Contracts\Store\QueriesAll;
use LaravelJsonApi\Core\Contracts\Store\QueryBuilder;
use LaravelJsonApi\Core\Contracts\Store\Repository;
use LaravelJsonApi\Core\Support\Str;
use LogicException;

class Store
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
    public function __call($name, $arguments)
    {
        return $this->resources(
            Str::dasherize($name)
        );
    }

    /**
     * Get a model by JSON API resource type and id.
     *
     * @param string $resourceType
     * @param string $resourceId
     * @return Model|mixed|null
     */
    public function find(string $resourceType, string $resourceId)
    {
        return $this
            ->resources($resourceType)
            ->find($resourceId);
    }

    /**
     * Does a model exist for the supplied resource type and id?
     *
     * @param string $resourceType
     * @param string $resourceId
     * @return bool
     */
    public function exists(string $resourceType, string $resourceId): bool
    {
        return $this
            ->resources($resourceType)
            ->exists($resourceId);
    }

    /**
     * Query all resources by JSON API resource type.
     *
     * @param string $resourceType
     * @return QueryBuilder
     */
    public function query(string $resourceType): QueryBuilder
    {
        $repository = $this->resources($resourceType);

        if ($repository instanceof QueriesAll) {
            return $repository->query();
        }

        throw new LogicException("Querying all {$resourceType} resources is not supported.");
    }

    /**
     * Access a resource repository by its JSON API resource type.
     *
     * @param string $resourceType
     * @return Repository
     */
    public function resources(string $resourceType): Repository
    {
        return $this->schemas
            ->schemaFor($resourceType)
            ->repository();
    }
}
