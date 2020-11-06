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

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Traits\ForwardsCalls;
use InvalidArgumentException;
use LaravelJsonApi\Core\Contracts\Pagination\Page;
use LaravelJsonApi\Core\Query\IncludePaths;
use LaravelJsonApi\Core\Query\QueryParameters;
use LaravelJsonApi\Core\Query\RelationshipPath;
use LaravelJsonApi\Core\Query\SortField;
use LaravelJsonApi\Core\Query\SortFields;
use LaravelJsonApi\Eloquent\Contracts\Filter;
use LaravelJsonApi\Eloquent\Contracts\Paginator;
use LaravelJsonApi\Eloquent\Contracts\Sortable;
use LogicException;
use RuntimeException;

/**
 * Class Builder
 *
 * @mixin EloquentBuilder
 */
class Builder
{

    use ForwardsCalls;

    /**
     * @var Schema
     */
    private Schema $schema;

    /**
     * @var EloquentBuilder|Relation
     */
    private $query;

    /**
     * @var QueryParameters
     */
    private QueryParameters $parameters;

    /**
     * @var bool
     */
    private bool $singular = false;

    /**
     * Builder constructor.
     *
     * @param Schema $schema
     * @param EloquentBuilder|Relation $query
     */
    public function __construct(Schema $schema, $query)
    {
        $this->schema = $schema;
        $this->query = $query;
        $this->parameters = new QueryParameters();
    }

    /**
     * @param $name
     * @param $arguments
     * @return $this
     */
    public function __call($name, $arguments)
    {
        $result = $this->forwardCallTo($this->query, $name, $arguments);

        if ($result === $this->query) {
            return $this;
        }

        return $result;
    }

    /**
     * Apply the supplied JSON API filters.
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

        $actual = [];

        foreach ($this->schema->filters() as $filter) {
            if ($filter instanceof Filter) {
                if (array_key_exists($key = $filter->key(), $filters)) {
                    $filter->apply($this->query, $value = $filters[$key]);
                    $actual[$key] = $value;

                    if ($filter->isSingular()) {
                        $this->singular = true;
                    }
                }
                continue;
            }

            throw new RuntimeException(sprintf(
                'Schema %s has a filter that does not implement the filter contract.',
                $this->schema->type()
            ));
        }

        $this->parameters->withFilters($actual);

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
     * Set the relations that should be eager loaded using JSON API include paths.
     *
     * @param IncludePaths|RelationshipPath|array|string|null $includePaths
     * @return $this
     */
    public function with($includePaths): self
    {
        if (is_null($includePaths)) {
            $this->parameters->withoutIncludePaths();
            return $this;
        }

        $this->schema->loader()
            ->using($this->query)
            ->with($includePaths);

        $this->parameters->withIncludePaths($includePaths);

        return $this;
    }

    /**
     * Add a where clause using the JSON API resource id.
     *
     * @param string|array|Arrayable $resourceId
     * @return $this
     */
    public function whereResourceId($resourceId): self
    {
        $column = $this->query->qualifyColumn(
            $this->schema->idName()
        );

        if (is_string($resourceId)) {
            $this->query->where($column, '=', $resourceId);
            return $this;
        }

        if (is_array($resourceId) || $resourceId instanceof Arrayable) {
            $this->query->whereIn($column, $resourceId);
            return $this;
        }

        throw new InvalidArgumentException('Unexpected resource id value.');
    }

    /**
     * Has a singular filter been applied?
     *
     * @return bool
     */
    public function isSingular(): bool
    {
        return $this->singular;
    }

    /**
     * Return a page of models using JSON API page parameters.
     *
     * @param array $page
     * @return Page|mixed
     */
    public function paginate(array $page)
    {
        $paginator = $this->schema->pagination();

        if ($paginator instanceof Paginator) {
            return $paginator->paginate($this->query, $page)->withQuery(
                $this->parameters->withPagination($page)->toArray()
            );
        }

        if ($paginator) {
            throw new LogicException(sprintf(
                'Expecting paginator for resource %s to be an Eloquent paginator.',
                $this->schema->type()
            ));
        }

        throw new LogicException(sprintf(
            'Resource %s does not support pagination.',
            $this->schema->type()
        ));
    }

    /**
     * @return QueryParameters
     */
    public function getQueryParameters(): QueryParameters
    {
        return $this->parameters;
    }

    /**
     * @return EloquentBuilder
     */
    public function toBase(): EloquentBuilder
    {
        if ($this->query instanceof Relation) {
            return $this->query->getQuery();
        }

        return $this->query;
    }

}
