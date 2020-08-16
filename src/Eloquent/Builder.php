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
use LaravelJsonApi\Core\Contracts\Query\QueryParameters as QueryParametersContract;
use LaravelJsonApi\Core\Contracts\Store\QueryBuilder;
use LaravelJsonApi\Core\Query\IncludePaths;
use LaravelJsonApi\Core\Query\QueryParameters;
use LaravelJsonApi\Core\Query\RelationshipPath;
use LaravelJsonApi\Core\Query\SortField;
use LaravelJsonApi\Core\Query\SortFields;
use LaravelJsonApi\Eloquent\Contracts\Filter;
use LaravelJsonApi\Eloquent\Contracts\Sortable;
use LogicException;
use function array_key_exists;
use function is_null;
use function sprintf;

class Builder implements QueryBuilder
{

    /**
     * @var Schema
     */
    private Schema $schema;

    /**
     * @var EloquentBuilder
     */
    private EloquentBuilder $query;

    /**
     * @var QueryParameters
     */
    private QueryParameters $parameters;

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
     * Apply the provided query parameters.
     *
     * @param QueryParametersContract $query
     * @return $this
     */
    public function using(QueryParametersContract $query): self
    {
        return $this
            ->with($query->includePaths())
            ->filter($query->filter())
            ->sort($query->sortFields());
    }

    /**
     * Filter models using JSON API filter parameters.
     *
     * @param array|null $filters
     * @return $this
     */
    public function filter(?array $filters): self
    {
        if (is_null($filters)) {
            $this->parameters->withoutFilters();
            return $this;
        }

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
     * @param SortFields|SortField|array|string|null $fields
     * @return $this
     */
    public function sort($fields): self
    {
        if (is_null($fields)) {
            $this->parameters->withoutSortFields();
            return $this;
        }

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
     * Eager load resources using the provided JSON API include paths.
     *
     * @param IncludePaths|RelationshipPath|array|string|null $includePaths
     * @return $this
     */
    public function with($includePaths): self
    {
        if (is_null($includePaths)) {
            $this->parameters->withoutIncludePaths();
        } else {
            $this->parameters->withIncludePaths($includePaths);
        }

        return $this;
    }

    /**
     * Execute the query and get the first result.
     *
     * @return Model|null
     */
    public function first(): ?Model
    {
        $this->eagerLoad();

        return $this->query->first();
    }

    /**
     * Execute the query and get models.
     *
     * @return EloquentCollection
     */
    public function get(): EloquentCollection
    {
        $this->eagerLoad();

        return $this->query->get();
    }

    /**
     * Return a page of models using JSON API page parameters.
     *
     * @param array $page
     * @return Page
     */
    public function paginate(array $page): Page
    {
        $this->eagerLoad();

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
     * Execute the query, paginating results only if page parameters are provided.
     *
     * @param array|null $page
     * @return EloquentCollection|Page
     */
    public function getOrPaginate(?array $page): iterable
    {
        if (empty($page)) {
            return $this->get();
        }

        return $this->paginate($page);
    }

    /**
     * @return QueryParameters
     */
    public function toQuery(): QueryParameters
    {
        return clone $this->parameters;
    }

    /**
     * @return EloquentBuilder
     */
    public function toBase()
    {
        return $this->query;
    }

    /**
     * Eager load relations.
     *
     * @return void
     */
    private function eagerLoad(): void
    {
        if ($includePaths = $this->parameters->includePaths()) {
            $loader = new EagerLoader($this->schema, $includePaths);
            $this->query->with($loader->all());
        }
    }
}
