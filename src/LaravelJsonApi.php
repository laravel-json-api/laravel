<?php
/*
 * Copyright 2023 Cloud Creativity Limited
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

namespace LaravelJsonApi\Laravel;

use Illuminate\Support\Arr;
use InvalidArgumentException;
use LaravelJsonApi\Core\Auth\AuthorizerResolver;
use LaravelJsonApi\Core\Query\Custom\ExtendedQueryParameters;
use LaravelJsonApi\Core\Resources\ResourceResolver;
use LaravelJsonApi\Eloquent\Resources\Relation;
use LaravelJsonApi\Laravel\Http\Requests\RequestResolver;

final class LaravelJsonApi
{

    /**
     * Register an authorizer for a resource type or types.
     *
     * @param string $authorizerClass
     * @param string|string[] $schemaClasses
     * @return LaravelJsonApi
     */
    public static function registerAuthorizer(string $authorizerClass, $schemaClasses): self
    {
        foreach (Arr::wrap($schemaClasses) as $schemaClass) {
            AuthorizerResolver::register($schemaClass, $authorizerClass);
        }

        return new self();
    }

    /**
     * Set the default authorizer implementation.
     *
     * @param string $authorizerClass
     * @return LaravelJsonApi
     */
    public static function defaultAuthorizer(string $authorizerClass): self
    {
        AuthorizerResolver::useDefault($authorizerClass);

        return new self();
    }

    /**
     * Set the default resource class.
     *
     * @param string $resourceClass
     * @return LaravelJsonApi
     */
    public static function defaultResource(string $resourceClass): self
    {
        ResourceResolver::useDefault($resourceClass);

        return new self();
    }

    /**
     * Register a HTTP query class for the supplied resource type or types.
     *
     * @param string $queryClass
     * @param $resourceTypes
     * @return $this
     */
    public static function registerQuery(string $queryClass, $resourceTypes): self
    {
        foreach (Arr::wrap($resourceTypes) as $resourceType) {
            RequestResolver::register(RequestResolver::QUERY, $resourceType, $queryClass);
        }

        return new self();
    }

    /**
     * Set the default query class implementation.
     *
     * @param string $queryClass
     * @return static
     */
    public static function defaultQuery(string $queryClass): self
    {
        RequestResolver::useDefault(RequestResolver::QUERY, $queryClass);

        return new self();
    }

    /**
     * Register a HTTP collection query class for the supplied resource type or types.
     *
     * @param string $queryClass
     * @param $resourceTypes
     * @return $this
     */
    public static function registerCollectionQuery(string $queryClass, $resourceTypes): self
    {
        foreach (Arr::wrap($resourceTypes) as $resourceType) {
            RequestResolver::register(RequestResolver::COLLECTION_QUERY, $resourceType, $queryClass);
        }

        return new self();
    }

    /**
     * Set the default collection query class implementation.
     *
     * @param string $queryClass
     * @return static
     */
    public static function defaultCollectionQuery(string $queryClass): self
    {
        RequestResolver::useDefault(RequestResolver::COLLECTION_QUERY, $queryClass);

        return new self();
    }

    /**
     * Register a HTTP request class for the supplied resource type or types.
     *
     * @param string $queryClass
     * @param $resourceTypes
     * @return $this
     */
    public static function registerRequest(string $queryClass, $resourceTypes): self
    {
        foreach (Arr::wrap($resourceTypes) as $resourceType) {
            RequestResolver::register(RequestResolver::REQUEST, $resourceType, $queryClass);
        }

        return new self();
    }

    /**
     * Set the query parameter name for the countable implementation.
     *
     * @param string $parameter
     * @return static
     */
    public static function withCountQueryParameter(string $parameter): self
    {
        if (!empty($parameter)) {
            ExtendedQueryParameters::withCount($parameter);
            return new self();
        }

        throw new InvalidArgumentException('Expecting a non-empty string for the countable query parameter.');
    }

    /**
     * Set the relationship meta key for the countable implementation.
     *
     * @param string $key
     * @return static
     */
    public static function withCountMetaKey(string $key): self
    {
        if (!empty($key)) {
            Relation::withCount($key);
            return new self();
        }

        throw new InvalidArgumentException('Expecting a non-empty string for the countable meta key.');
    }
}
