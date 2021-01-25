<?php
/*
 * Copyright 2021 Cloud Creativity Limited
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
use LaravelJsonApi\Core\Auth\AuthorizerResolver;

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
}
