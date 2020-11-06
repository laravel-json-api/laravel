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

namespace LaravelJsonApi\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\LazyCollection;
use LaravelJsonApi\Contracts\Pagination\Page;
use LaravelJsonApi\Contracts\Query\QueryParameters as QueryParametersContract;
use LaravelJsonApi\Contracts\Store\QueryAllBuilder as QueryBuilderContract;

class QueryAll implements QueryBuilderContract
{

    /**
     * @var Builder
     */
    private Builder $query;

    /**
     * QueryAll constructor.
     *
     * @param Builder $query
     */
    public function __construct(Builder $query)
    {
        $this->query = $query;
    }

    /**
     * @inheritDoc
     */
    public function using(QueryParametersContract $query): self
    {
        return $this
            ->with($query->includePaths())
            ->filter($query->filter())
            ->sort($query->sortFields());
    }

    /**
     * @inheritDoc
     */
    public function filter(?array $filters): self
    {
        $this->query->filter($filters);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function sort($fields): self
    {
        $this->query->sort($fields);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function with($includePaths): self
    {
        $this->query->with($includePaths);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function first(): ?Model
    {
        return $this->query->first();
    }

    /**
     * @inheritDoc
     */
    public function firstOrMany()
    {
        if ($this->query->isSingular()) {
            return $this->first();
        }

        return $this->cursor();
    }

    /**
     * @inheritDoc
     */
    public function cursor(): LazyCollection
    {
        return $this->query->cursor();
    }

    /**
     * @inheritDoc
     */
    public function paginate(array $page): Page
    {
        return $this->query->paginate($page);
    }

    /**
     * @inheritDoc
     */
    public function getOrPaginate(?array $page): iterable
    {
        if (empty($page)) {
            return $this->cursor();
        }

        return $this->paginate($page);
    }

    /**
     * @inheritDoc
     */
    public function firstOrPaginate(?array $page)
    {
        if (empty($page)) {
            return $this->firstOrMany();
        }

        return $this->paginate($page);
    }

}
