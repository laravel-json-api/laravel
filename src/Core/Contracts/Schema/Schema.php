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

namespace LaravelJsonApi\Core\Contracts\Schema;

use LaravelJsonApi\Core\Contracts\Pagination\Paginator;
use LaravelJsonApi\Core\Contracts\Store\Repository;

interface Schema
{

    /**
     * Get the JSON API resource type.
     *
     * @return string
     */
    public static function type(): string;

    /**
     * Get the fully-qualified class name of the model.
     *
     * @return string
     */
    public static function model(): string;

    /**
     * Get the fully-qualified class name of the resource.
     *
     * @return string
     */
    public static function resource(): string;

    /**
     * Get a repository for the resource.
     *
     * @return Repository
     */
    public function repository(): Repository;

    /**
     * Get the resource fields (attributes and relationships).
     *
     * @return iterable
     */
    public function fields(): iterable;

    /**
     * Get the resource attributes.
     *
     * @return iterable
     */
    public function attributes(): iterable;

    /**
     * Get the resource relationships.
     *
     * @return iterable
     */
    public function relationships(): iterable;

    /**
     * Get the filters for the resource.
     *
     * @return array
     */
    public function filters(): iterable;

    /**
     * Get the paginator to use when fetching collections of this resource.
     *
     * @return Paginator|null
     */
     public function pagination(): ?Paginator;
}
