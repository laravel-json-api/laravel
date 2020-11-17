<?php
/*
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

namespace LaravelJsonApi\Contracts\Schema;

use LaravelJsonApi\Contracts\Pagination\Paginator;
use LaravelJsonApi\Contracts\Store\Repository;
use Traversable;

interface Schema extends Traversable
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
     * Get the "id" field.
     *
     * @return ID
     */
    public function id(): ID;

    /**
     * Get all the field names.
     *
     * @return array
     */
    public function fieldNames(): array;

    /**
     * Does the named field exist?
     *
     * @param string $name
     * @return bool
     */
    public function isField(string $name): bool;

    /**
     * Get a field by name.
     *
     * @param string $name
     * @return Field
     */
    public function field(string $name): Field;

    /**
     * Get the resource attributes.
     *
     * @return Attribute[]|iterable
     */
    public function attributes(): iterable;

    /**
     * Get an attribute by name.
     *
     * @param string $name
     * @return Attribute
     */
    public function attribute(string $name): Attribute;

    /**
     * Does the named attribute exist?
     *
     * @param string $name
     * @return bool
     */
    public function isAttribute(string $name): bool;

    /**
     * Get the resource relationships.
     *
     * @return Relation[]|iterable
     */
    public function relationships(): iterable;

    /**
     * Get a relationship by name.
     *
     * @param string $name
     * @return Relation
     */
    public function relationship(string $name): Relation;

    /**
     * Does the named relationship exist?
     *
     * @param string $name
     * @return bool
     */
    public function isRelationship(string $name): bool;

    /**
     * Get the filters for the resource.
     *
     * @return Filter[]|iterable
     */
    public function filters(): iterable;

    /**
     * Get the paginator to use when fetching collections of this resource.
     *
     * @return Paginator|null
     */
     public function pagination(): ?Paginator;

    /**
     * Get the include paths supported by this resource.
     *
     * @return string[]|iterable
     */
     public function includePaths(): iterable;

    /**
     * Get the sparse fields that are supported by this resource.
     *
     * @return string[]|iterable
     */
     public function sparseFields(): iterable;

    /**
     * Get the parameters that can be used to sort this resource.
     *
     * @return string[]|iterable
     */
     public function sortable(): iterable;
}
