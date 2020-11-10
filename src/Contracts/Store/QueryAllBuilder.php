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

interface QueryAllBuilder extends QueryManyBuilder
{

    /**
     * Execute the query and get the first result.
     *
     * @return Model|object|null
     */
    public function first(): ?object;

    /**
     * Execute the query and return the result.
     *
     * If a singular filter has been applied, this method MUST return
     * the first matching model, or null.
     *
     * Otherwise, this method MUST return a cursor of all matching models.
     *
     * @return LazyCollection|Model|object|null
     */
    public function firstOrMany();

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
     * @return Model|object|Page|LazyCollection|iterable|null
     */
    public function firstOrPaginate(?array $page);
}
