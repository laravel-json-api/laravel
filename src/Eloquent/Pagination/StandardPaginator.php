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

namespace LaravelJsonApi\Eloquent\Pagination;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use LaravelJsonApi\Contracts\Pagination\Page as PageContract;
use LaravelJsonApi\Core\Pagination\Page;
use LaravelJsonApi\Eloquent\Contracts\Paginator;

class StandardPaginator implements Paginator
{

    /**
     * @var string
     */
    protected string $pageKey;

    /**
     * @var string
     */
    protected string  $perPageKey;

    /**
     * @var array|null
     */
    protected ?array $columns = null;

    /**
     * @var bool|null
     */
    protected ?bool $simplePagination = null;

    /**
     * @var string|null
     */
    protected ?string $metaKey;

    /**
     * @var string|null
     */
    protected ?string $primaryKey = null;

    /**
     * Fluent constructor.
     *
     * @return StandardPaginator
     */
    public static function make(): self
    {
        return new self();
    }

    /**
     * StandardStrategy constructor.
     */
    public function __construct()
    {
        $this->pageKey = 'number';
        $this->perPageKey = 'size';
        $this->metaKey = 'page';
    }

    /**
     * @inheritDoc
     */
    public function keys(): array
    {
        return [
            $this->pageKey,
            $this->perPageKey,
        ];
    }

    /**
     * Set the qualified column name that is being used for the resource's ID.
     *
     * @param string $keyName
     * @return $this
     */
    public function withQualifiedKeyName(string $keyName): self
    {
        $this->primaryKey = $keyName;

        return $this;
    }

    /**
     * @param string $key
     * @return $this
     */
    public function withPageKey(string $key): self
    {
        $this->pageKey = $key;

        return $this;
    }

    /**
     * @param string $key
     * @return $this
     */
    public function withPerPageKey(string $key): self
    {
        $this->perPageKey = $key;

        return $this;
    }

    /**
     * @param array|string $cols
     * @return $this;
     */
    public function withColumns($cols): self
    {
        $this->columns = $cols;

        return $this;
    }

    /**
     * @return $this
     */
    public function withSimplePagination(): self
    {
        $this->simplePagination = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function withLengthAwarePagination(): self
    {
        $this->simplePagination = false;

        return $this;
    }

    /**
     * Set the key for the paging meta.
     *
     * Use this to 'nest' the paging meta in a sub-key of the JSON API document's top-level meta object.
     * A string sets the key to use for nesting. Use `null` to indicate no nesting.
     *
     * @param string|null $key
     * @return $this
     */
    public function withMetaKey(?string $key): self
    {
        $this->metaKey = $key ?: null;

        return $this;
    }

    /**
     * Mark the paginator as not nesting page meta.
     *
     * @return $this
     */
    public function withoutNestedMeta(): self
    {
        return $this->withMetaKey(null);
    }

    /**
     * @inheritDoc
     */
    public function paginate($query, array $page): PageContract
    {
        $paginator = $this
            ->defaultOrder($query)
            ->query($query, $page);

        return (new Page($paginator))
            ->withNestedMeta($this->metaKey)
            ->withPageParam($this->pageKey)
            ->withPerPageParam($this->perPageKey);
    }

    /**
     * @param array $page
     * @return int
     */
    protected function getPerPage(array $page): int
    {
        $perPage = $page[$this->perPageKey] ?? 0;

        return (int) $perPage;
    }

    /**
     * Get the default per-page value for the query.
     *
     * If the query is an Eloquent builder, we can pass in `null` as the default,
     * which then delegates to the model to get the default. Otherwise the Laravel
     * standard default is 15.
     *
     * @param $query
     * @return int|null
     */
    protected function getDefaultPerPage($query)
    {
        return $query instanceof EloquentBuilder ? null : 15;
    }

    /**
     * @return array
     */
    protected function getColumns()
    {
        return $this->columns ?: ['*'];
    }

    /**
     * @return bool
     */
    protected function isSimplePagination()
    {
        return (bool) $this->simplePagination;
    }

    /**
     * @param $query
     * @return bool
     */
    protected function willSimplePaginate($query)
    {
        return $this->isSimplePagination() && method_exists($query, 'simplePaginate');
    }

    /**
     * Apply a deterministic order to the page.
     *
     * @param QueryBuilder|EloquentBuilder|Relation $query
     * @return $this
     * @see https://github.com/cloudcreativity/laravel-json-api/issues/313
     */
    protected function defaultOrder($query)
    {
        if ($this->doesRequireOrdering($query)) {
            $query->orderBy($this->primaryKey);
        }

        return $this;
    }

    /**
     * Do we need to apply a deterministic order to the query?
     *
     * If the primary key has not been used for a sort order already, we use it
     * to ensure the page has a deterministic order.
     *
     * @param QueryBuilder|EloquentBuilder|Relation $query
     * @return bool
     */
    protected function doesRequireOrdering($query)
    {
        if (!$this->primaryKey) {
            return false;
        }

        $query = ($query instanceof Relation) ? $query->getBaseQuery() : $query->getQuery();

        return !collect($query->orders ?: [])->contains(function (array $order) {
            $col = $order['column'] ?? '';
            return $this->primaryKey === $col;
        });
    }

    /**
     * @param QueryBuilder|EloquentBuilder|Relation $query
     * @param array $page
     * @return mixed
     */
    protected function query($query, array $page)
    {
        $size = $this->getPerPage($page) ?: $this->getDefaultPerPage($query);
        $cols = $this->getColumns();

        return $this->willSimplePaginate($query) ?
            $query->simplePaginate($size, $cols, $this->pageKey) :
            $query->paginate($size, $cols, $this->pageKey);
    }
}
