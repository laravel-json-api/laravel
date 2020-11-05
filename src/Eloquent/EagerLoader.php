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

use Generator;
use Illuminate\Database\Eloquent\Model;
use LaravelJsonApi\Core\Contracts\Schema\Container;
use LaravelJsonApi\Core\Query\IncludePaths;
use LogicException;
use function iterator_to_array;

class EagerLoader
{

    /**
     * @var Container
     */
    private Container $schemas;

    /**
     * @var Schema
     */
    private Schema $schema;

    /**
     * @var Model|null
     */
    private ?Model $model = null;

    /**
     * @var \Illuminate\Database\Eloquent\Builder|null
     */
    private $query;

    /**
     * EagerLoader constructor.
     *
     * @param Container $schemas
     * @param Schema $schema
     */
    public function __construct(Container $schemas, Schema $schema)
    {
        $this->schemas = $schemas;
        $this->schema = $schema;
    }

    /**
     * @param Model|\Illuminate\Database\Eloquent\Builder $modelOrQuery
     * @return $this
     */
    public function using($modelOrQuery): self
    {
        if ($modelOrQuery instanceof Model) {
            $this->model = $modelOrQuery;
        } else {
            $this->query = $modelOrQuery;
        }

        return $this;
    }

    /**
     * @param $includePaths
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function with($includePaths)
    {
        if ($this->query) {
            return $this->query->with(
                $this->toRelations($includePaths)
            );
        }

        throw new LogicException('No query to load relations on.');
    }

    /**
     * @param $includePaths
     * @return Model
     */
    public function load($includePaths): Model
    {
        if ($this->model) {
            return $this->model->load(
                $this->toRelations($includePaths)
            );
        }

        throw new LogicException('No model to load relations on.');
    }

    /**
     * @param $includePaths
     * @return Model
     */
    public function loadMissing($includePaths): Model
    {
        if ($this->model) {
            return $this->model->loadMissing(
                $this->toRelations($includePaths)
            );
        }

        throw new LogicException('No model to load relations on.');
    }

    /**
     * @param $includePaths
     * @return array
     */
    public function toRelations($includePaths): array
    {
        return iterator_to_array($this->cursor($includePaths));
    }

    /**
     * @param mixed $includePaths
     * @return Generator
     */
    public function cursor($includePaths): Generator
    {
        foreach (IncludePaths::cast($includePaths) as $path) {
            yield (string) new EagerLoadPath($this->schemas, $this->schema, $path);
        }
    }

}
