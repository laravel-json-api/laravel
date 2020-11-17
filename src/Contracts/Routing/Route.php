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

namespace LaravelJsonApi\Contracts\Routing;

use Illuminate\Database\Eloquent\Model;
use LaravelJsonApi\Contracts\Schema\Relation;
use LaravelJsonApi\Contracts\Schema\Schema;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

interface Route
{

    /**
     * Get the resource type.
     *
     * @return string
     */
    public function resourceType(): string;

    /**
     * Get the resource id or the model (if bindings have been substituted).
     *
     * @return Model|mixed|string|null
     */
    public function modelOrResourceId();

    /**
     * Does the URL have a resource id?
     *
     * @return bool
     */
    public function hasResourceId(): bool;

    /**
     * Get the resource id.
     *
     * @return string
     */
    public function resourceId(): string;

    /**
     * Get the resource model.
     *
     * @return Model|object
     */
    public function model(): object;

    /**
     * Get the field name for a relationship URL.
     *
     * @return string
     */
    public function fieldName(): string;

    /**
     * Get the schema for the current route.
     *
     * @return Schema
     */
    public function schema(): Schema;

    /**
     * Does the URL have a relation?
     *
     * @return bool
     */
    public function hasRelation(): bool;

    /**
     * Get the inverse schema for a relationship route.
     *
     * For example, the URL `/api/posts/123/comments` would
     * return the comments schema as the inverse schema.
     *
     * @return Schema
     */
    public function inverse(): Schema;

    /**
     * Get the relation for a relationship URL.
     *
     * @return Relation
     */
    public function relation(): Relation;

    /**
     * Substitute the route bindings onto the Laravel route.
     *
     * @return void
     * @throws HttpExceptionInterface
     */
    public function substituteBindings(): void;
}
