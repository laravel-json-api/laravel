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

namespace LaravelJsonApi\Contracts\Store;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use LaravelJsonApi\Contracts\Query\QueryParameters;
use LaravelJsonApi\Core\Query\IncludePaths;
use LaravelJsonApi\Core\Query\RelationshipPath;

interface ToManyBuilder
{

    /**
     * Apply the provided query parameters.
     *
     * @param QueryParameters $query
     * @return $this
     */
    public function using(QueryParameters $query): self;

    /**
     * Eager load resources using the provided JSON API include paths.
     *
     * @param IncludePaths|RelationshipPath|array|string|null $includePaths
     * @return $this
     */
    public function with($includePaths): self;

    /**
     * Completely replace every member of the relationship with the specified members.
     *
     * @param array $identifiers
     * @return EloquentCollection|iterable
     *      the related models that were used to replace the relationship.
     */
    public function sync(array $identifiers): iterable;

    /**
     * Add the specified members to the relationship unless they are already present.
     *
     * @param array $identifiers
     * @return EloquentCollection|iterable
     *      the related models that were added to the relationship.
     */
    public function attach(array $identifiers): iterable;

    /**
     * Delete the specified members from the relationship.
     *
     * @param array $identifiers
     * @return EloquentCollection|iterable
     *      the related models that were removed from the relationship.
     */
    public function detach(array $identifiers): iterable;
}
