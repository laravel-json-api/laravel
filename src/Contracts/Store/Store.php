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

namespace LaravelJsonApi\Contracts\Store;

use Illuminate\Database\Eloquent\Model;

interface Store
{
    /**
     * Get a model by JSON API resource type and id.
     *
     * @param string $resourceType
     * @param string $resourceId
     * @return Model|object|null
     */
    public function find(string $resourceType, string $resourceId): ?object;

    /**
     * Does a model exist for the supplied resource type and id?
     *
     * @param string $resourceType
     * @param string $resourceId
     * @return bool
     */
    public function exists(string $resourceType, string $resourceId): bool;

    /**
     * Query all resources by JSON API resource type.
     *
     * @param string $resourceType
     * @return QueryAllBuilder
     */
    public function queryAll(string $resourceType): QueryAllBuilder;

    /**
     * Query one resource by JSON API resource type.
     *
     * @param string $resourceType
     * @param Model|object|string $modelOrResourceId
     * @return QueryOneBuilder
     */
    public function queryOne(string $resourceType, $modelOrResourceId): QueryOneBuilder;

    /**
     * Query a to-one relationship.
     *
     * @param string $resourceType
     * @param $modelOrResourceId
     * @param string $fieldName
     * @return QueryOneBuilder
     */
    public function queryToOne(string $resourceType, $modelOrResourceId, string $fieldName): QueryOneBuilder;

    /**
     * Query a to-many relationship.
     *
     * @param string $resourceType
     * @param $modelOrResourceId
     * @param string $fieldName
     * @return QueryManyBuilder
     */
    public function queryToMany(string $resourceType, $modelOrResourceId, string $fieldName): QueryManyBuilder;

    /**
     * Create a new resource.
     *
     * @param string $resourceType
     * @return ResourceBuilder
     */
    public function create(string $resourceType): ResourceBuilder;

    /**
     * Update an existing resource.
     *
     * @param string $resourceType
     * @param Model|object|string $modelOrResourceId
     * @return ResourceBuilder
     */
    public function update(string $resourceType, $modelOrResourceId): ResourceBuilder;

    /**
     * Delete an existing resource.
     *
     * @param string $resourceType
     * @param Model|object|string $modelOrResourceId
     * @return void
     */
    public function delete(string $resourceType, $modelOrResourceId): void;

    /**
     * Modify a to-one relation.
     *
     * @param string $resourceType
     * @param Model|object|string $modelOrResourceId
     * @param string $fieldName
     * @return ToOneBuilder
     */
    public function modifyToOne(string $resourceType, $modelOrResourceId, string $fieldName): ToOneBuilder;

    /**
     * Modify a to-many relation.
     *
     * @param string $resourceType
     * @param Model|object|string $modelOrResourceId
     * @param string $fieldName
     * @return ToManyBuilder
     */
    public function modifyToMany(string $resourceType, $modelOrResourceId, string $fieldName): ToManyBuilder;

    /**
     * Access a resource repository by its JSON API resource type.
     *
     * @param string $resourceType
     * @return Repository
     */
    public function resources(string $resourceType): Repository;
}
