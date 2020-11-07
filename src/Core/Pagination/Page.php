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

namespace LaravelJsonApi\Core\Pagination;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use LaravelJsonApi\Contracts\Pagination\Page as PageContract;
use LaravelJsonApi\Core\Document\Link;
use LaravelJsonApi\Core\Document\Links;
use LaravelJsonApi\Core\Responses\PaginatedResourceResponse;
use function array_filter;
use function collect;
use function count;
use function is_null;

class Page implements PageContract
{

    /**
     * @var Paginator|LengthAwarePaginator
     */
    private Paginator $paginator;

    /**
     * @var array|null
     */
    private ?array $queryParameters = null;

    /**
     * @var string|null
     */
    private ?string $metaKey = null;

    /**
     * @var string
     */
    private string $pageParam = 'number';

    /**
     * @var string
     */
    private string $perPageParam = 'size';

    /**
     * @param PageContract|Paginator $page
     * @return PageContract
     */
    public static function cast($page): PageContract
    {
        if ($page instanceof PageContract) {
            return $page;
        }

        if ($page instanceof Paginator) {
            return new self($page);
        }

        throw new InvalidArgumentException('Expecting a JSON API page or a Laravel paginator.');
    }

    /**
     * Page constructor.
     *
     * @param Paginator $paginator
     */
    public function __construct(Paginator $paginator)
    {
        $this->paginator = $paginator;
    }

    /**
     * @inheritDoc
     */
    public function meta(): array
    {
        $meta = collect([
            'current_page' => (int) $this->paginator->currentPage(),
            'from' => (int) $this->paginator->firstItem(),
            'last_page' => $this->isLengthAware() ? (int) $this->paginator->lastPage() : null,
            'per_page' => (int) $this->paginator->perPage(),
            'to' => (int) $this->paginator->lastItem(),
            'total' => $this->isLengthAware() ? (int) $this->paginator->total() : null,
        ])->reject(function ($value) {
            return is_null($value);
        })->all();

        if ($this->metaKey) {
            return [$this->metaKey => $meta];
        }

        return $meta;
    }

    /**
     * @inheritDoc
     */
    public function links(): Links
    {
        return new Links(...array_filter([
            $this->first(),
            $this->prev(),
            $this->next(),
            $this->last(),
        ]));
    }

    /**
     * @return Link
     */
    public function first(): Link
    {
        return new Link('first', $this->url(1));
    }

    /**
     * @return Link|null
     */
    public function prev(): ?Link
    {
        if (1 < $this->paginator->currentPage()) {
            return new Link('prev', $this->url(
                $this->paginator->currentPage() - 1
            ));
        }

        return null;
    }

    /**
     * @return Link|null
     */
    public function next(): ?Link
    {
        if ($this->isLengthAware() && $this->paginator->hasMorePages()) {
            return new Link('next', $this->url(
                $this->paginator->currentPage() + 1
            ));
        }

        return null;
    }

    /**
     * @return Link|null
     */
    public function last(): ?Link
    {
        if ($this->isLengthAware()) {
            return new Link('last', $this->url($this->paginator->lastPage()));
        }

        return null;
    }

    /**
     * @param int $page
     * @return string
     */
    public function url(int $page): string
    {
        $params = collect($this->queryParameters)->put('page', [
            $this->pageParam => $page,
            $this->perPageParam => $this->paginator->perPage(),
        ])->sortKeys()->all();

        return $this->paginator->path() . '?' . Arr::query($params);
    }

    /**
     * @inheritDoc
     */
    public function withQuery(iterable $query): PageContract
    {
        $this->queryParameters = collect($query)->all();

        return $this;
    }

    /**
     * Nest page meta using the provided key.
     *
     * @param string|null $key
     * @return $this
     */
    public function withNestedMeta(?string $key = 'page'): self
    {
        $this->metaKey = $key;

        return $this;
    }

    /**
     * Set the key for the page number parameter.
     *
     * @param string $key
     * @return $this
     */
    public function withPageParam(string $key): self
    {
        if (empty($key)) {
            throw new \InvalidArgumentException('Page parameter cannot be an empty string.');
        }

        $this->pageParam = $key;

        return $this;
    }

    /**
     * Set the key for the per-page parameter.
     *
     * @param string $key
     * @return $this
     */
    public function withPerPageParam(string $key): self
    {
        if (empty($key)) {
            throw new \InvalidArgumentException('Per-page parameter cannot be an empty string.');
        }

        $this->perPageParam = $key;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getIterator()
    {
        yield from $this->paginator;
    }

    /**
     * @inheritDoc
     */
    public function count()
    {
        return count($this->paginator);
    }

    /**
     * @param $request
     * @return PaginatedResourceResponse
     */
    public function prepareResponse($request): PaginatedResourceResponse
    {
        return new PaginatedResourceResponse($this);
    }

    /**
     * @inheritDoc
     */
    public function toResponse($request)
    {
        return $this->prepareResponse($request)->toResponse($request);
    }

    /**
     * @return bool
     */
    protected function isLengthAware(): bool
    {
        return $this->paginator instanceof LengthAwarePaginator;
    }

}
