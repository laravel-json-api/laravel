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

namespace LaravelJsonApi\Core\Contracts\Query;

use LaravelJsonApi\Core\Query\FieldSets;
use LaravelJsonApi\Core\Query\IncludePaths;
use LaravelJsonApi\Core\Query\SortFields;

interface QueryParameters
{

    /**
     * Get the JSON API include paths.
     *
     * @return IncludePaths|null
     */
    public function includePaths(): ?IncludePaths;

    /**
     * Get the JSON API sparse field sets.
     *
     * @return FieldSets|null
     */
    public function sparseFieldSets(): ?FieldSets;

    /**
     * Get the JSON API sort fields.
     *
     * @return SortFields|null
     */
    public function sortFields(): ?SortFields;

    /**
     * Get the JSON API page parameters.
     *
     * @return array|null
     */
    public function page(): ?array;

    /**
     * Get the JSON API filter parameters.
     *
     * @return array|null
     */
    public function filter(): ?array;

}
