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

namespace LaravelJsonApi\Core\Contracts\Pagination;

use Countable;
use Illuminate\Contracts\Support\Responsable;
use IteratorAggregate;
use LaravelJsonApi\Core\Document\Links;

interface Page extends IteratorAggregate, Countable, Responsable
{

    /**
     * Get the page meta.
     *
     * @return array
     */
    public function meta(): array;

    /**
     * Get the page links.
     *
     * @return Links
     */
    public function links(): Links;

    /**
     * Specify the query string parameters that should be present on pagination links.
     *
     * @param iterable $query
     * @return $this
     */
    public function withQuery(iterable $query): self;
}
