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

use Illuminate\Database\Eloquent\Model;
use LaravelJsonApi\Contracts\Query\QueryParameters;
use LaravelJsonApi\Core\Query\IncludePaths;
use LaravelJsonApi\Core\Query\RelationshipPath;

interface QueryOneBuilder
{

    /**
     * Apply the provided query parameters.
     *
     * @param QueryParameters $query
     * @return $this
     */
    public function using(QueryParameters $query): self;

    /**
     * Filter the model using JSON API filter parameters.
     *
     * @param array|null $filters
     * @return $this
     */
    public function filter(?array $filters): self;

    /**
     * Eager load resources using the provided JSON API include paths.
     *
     * @param IncludePaths|RelationshipPath|array|string|null $includePaths
     * @return $this
     */
    public function with($includePaths): self;

    /**
     * Execute the query and get the first result.
     *
     * @return Model|object|null
     */
    public function first(): ?object;
}
