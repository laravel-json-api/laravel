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

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use LaravelJsonApi\Core\Contracts\Pagination\Page;
use LaravelJsonApi\Core\Query\QueryParameters;
use LaravelJsonApi\Core\Query\SortField;
use LaravelJsonApi\Core\Query\SortFields;
use LaravelJsonApi\Eloquent\Contracts\Filter;
use LaravelJsonApi\Eloquent\Contracts\Sortable;
use LogicException;
use function array_key_exists;
use function sprintf;

class Builder
{

    /**
     * @var Schema
     */
    private $schema;

    /**
     * @var EloquentBuilder
     */
    private $query;

    /**
     * @var QueryParameters
     */
    private $parameters;

    /**
     * Builder constructor.
     *
     * @param Schema $schema
     * @param EloquentBuilder $query
     */
    public function __construct(Schema $schema, $query)
    {
        $this->schema = $schema;
        $this->query = $query;
        $this->parameters = new QueryParameters();
    }

    /**
     * Filter models using JSON API filter parameters.
     *
     * @param array $filters
     * @return $this
     */
    public function filter(array $filters): self
    {
        /** @var Filter $filter */
        foreach ($this->schema->filters() as $filter) {
            if (array_key_exists($filter->key(), $filters)) {
                $filter->apply($this->query, $filters[$filter->key()]);
            }
        }

        $this->parameters->withFilters($filters);

        return $this;
    }

    /**
     * Sort models using JSON API sort fields.
     *
     * @param $fields
     * @return $this
     */
    public function sort($fields): self
    {
        $fields = SortFields::cast($fields);

        /** @var SortField $sort */
        foreach ($fields as $sort) {
            $field = $this->schema->attribute($sort->name());

            if ($field->isSortable() && $field instanceof Sortable) {
                $field->sort($this->query, $sort->isAscending());
                continue;
            }

            throw new LogicException(sprintf(
                'Field %s is not sortable on resource type %s.',
                $sort->name(),
                $this->schema->type()
            ));
        }

        $this->parameters->withSortFields($fields);

        return $this;
    }

    /**
     * Execute the query and get the first result.
     *
     * @return Model|null
     */
    public function first(): ?Model
    {
        return $this->query->first();
    }

    /**
     * Execute the query and get models.
     *
     * @return EloquentCollection
     */
    public function get(): EloquentCollection
    {
        return $this->query->get();
    }

    /**
     * Execute the query, paginating results only if page parameters are provided.
     *
     * @param array|null $page
     * @return EloquentCollection|Page
     */
    public function getOrPaginate(?array $page)
    {
        if (empty($page)) {
            return $this->get();
        }

        return $this->paginate($page);
    }

    /**
     * Return a page of models using JSON API page parameters.
     *
     * @param array $page
     * @return Page
     */
    public function paginate(array $page): Page
    {
        if ($paginator = $this->schema->pagination()) {
            return $paginator->paginate($this->query, $page)->withQuery(
                $this->parameters->withPagination($page)->toArray()
            );
        }

        throw new LogicException(sprintf(
            'Resource %s does not support pagination.',
            $this->schema->type()
        ));
    }

    /**
     * @return QueryParameters
     */
    public function toQuery(): QueryParameters
    {
        return $this->toQuery();
    }

    /**
     * @return EloquentBuilder
     */
    public function toBase()
    {
        return $this->query;
    }
}
