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
use Illuminate\Support\LazyCollection;
use LaravelJsonApi\Contracts\Pagination\Page;
use LaravelJsonApi\Contracts\Query\QueryParameters;
use LaravelJsonApi\Core\Query\IncludePaths;
use LaravelJsonApi\Core\Query\RelationshipPath;
use LaravelJsonApi\Core\Query\SortField;
use LaravelJsonApi\Core\Query\SortFields;

interface QueryAllBuilder
{

    /**
     * Apply the provided query parameters.
     *
     * @param QueryParameters $query
     * @return $this
     */
    public function using(QueryParameters $query): self;

    /**
     * Filter models using JSON API filter parameters.
     *
     * @param array|null $filters
     * @return $this
     */
    public function filter(?array $filters): self;

    /**
     * Sort models using JSON API sort fields.
     *
     * @param SortFields|SortField|array|string|null $fields
     * @return $this
     */
    public function sort($fields): self;

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
     * @return Model|mixed|null
     */
    public function first();

    /**
     * Execute the query and return the result.
     *
     * If a singular filter has been applied, this method MUST return
     * the first matching model, or null.
     *
     * Otherwise, this method MUST return a cursor of all matching models.
     *
     * @return Model|LazyCollection|null
     */
    public function firstOrMany();

    /**
     * Get a lazy collection for the query.
     *
     * @return LazyCollection
     */
    public function cursor(): LazyCollection;

    /**
     * Return a page of models using JSON API page parameters.
     *
     * @param array $page
     * @return Page
     */
    public function paginate(array $page): Page;

    /**
     * Execute the query, paginating results only if page parameters are provided.
     *
     * @param array|null $page
     * @return Page|LazyCollection|iterable
     */
    public function getOrPaginate(?array $page): iterable;

    /**
     * Execute the query.
     *
     * If the supplied page variable is empty, this method MUST return:
     * - the first matching model or null if a singular filter has been applied; OR
     * - a cursor of matching models.
     *
     * If the supplied page variable is not empty, this method MUST return
     * a page of matching models.
     *
     * @param array|null $page
     * @return Model|Page|iterable|null
     */
    public function firstOrPaginate(?array $page);
}
