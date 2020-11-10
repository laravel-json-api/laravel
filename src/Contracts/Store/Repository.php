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

namespace LaravelJsonApi\Contracts\Store;

use Illuminate\Database\Eloquent\Model;

interface Repository
{

    /**
     * Get the model for the supplied resource id.
     *
     * @param string $resourceId
     * @return Model|object|null
     */
    public function find(string $resourceId): ?object;

    /**
     * Get the models for the supplied resource ids.
     *
     * @param string[] $resourceIds
     * @return Model[]|object[]|iterable
     */
    public function findMany(array $resourceIds): iterable;

    /**
     * Find the supplied model or throw a runtime exception if it does not exist.
     *
     * @param string $resourceId
     * @return Model|object
     */
    public function findOrFail(string $resourceId): object;

    /**
     * Does a model with the supplied resource id exist?
     *
     * @param string $resourceId
     * @return bool
     */
    public function exists(string $resourceId): bool;
}
