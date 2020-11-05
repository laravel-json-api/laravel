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
use LaravelJsonApi\Core\Contracts\Query\QueryParameters as QueryParametersContract;
use LaravelJsonApi\Core\Contracts\Store\QueryOneBuilder as QueryOneBuilderContract;

class QueryOne implements QueryOneBuilderContract
{

    /**
     * @var Schema
     */
    private Schema $schema;

    /**
     * @var Builder
     */
    private Builder $query;

    /**
     * @var Model|null
     */
    private ?Model $model;

    /**
     * @var string
     */
    private string $resourceId;

    /**
     * @var array|null
     */
    private ?array $filters = null;

    /**
     * @var mixed|null
     */
    private $includePaths = null;

    /**
     * QueryOne constructor.
     *
     * @param Schema $schema
     * @param Builder $query
     * @param Model|null $model
     * @param string $resourceId
     */
    public function __construct(
        Schema $schema,
        Builder $query,
        ?Model $model,
        string $resourceId
    ) {
        $this->schema = $schema;
        $this->query = $query;
        $this->model = $model;
        $this->resourceId = $resourceId;
    }

    /**
     * @inheritDoc
     */
    public function using(QueryParametersContract $query): QueryOneBuilderContract
    {
        return $this
            ->filter($query->filter())
            ->with($query->includePaths());
    }

    /**
     * @inheritDoc
     */
    public function filter(?array $filters): QueryOneBuilderContract
    {
        $this->filters = $filters;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function with($includePaths): QueryOneBuilderContract
    {
        $this->includePaths = $includePaths;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function first()
    {
        if ($this->model && empty($this->filters)) {
            return $this->schema->loader()
                ->using($this->model)
                ->loadMissing($this->includePaths);
        }

        return $this->query
            ->whereResourceId($this->resourceId)
            ->filter($this->filters)
            ->with($this->includePaths)
            ->first();
    }

}
