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
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

class Repository
{

    /**
     * @var Schema
     */
    private $schema;

    /**
     * @var Model
     */
    private $model;

    /**
     * Repository constructor.
     *
     * @param Schema $schema
     */
    public function __construct(Schema $schema)
    {
        $this->schema = $schema;
        $this->model = $this->newInstance();
    }

    /**
     * Get the model for the supplied resource id.
     *
     * @param string $resourceId
     * @return Model|null
     */
    public function find(string $resourceId): ?Model
    {
        return $this->newQuery()->where(
            $this->idName(),
            $resourceId
        )->first();
    }

    /**
     * Does a resource with the supplied id exist?
     *
     * @param string $resourceId
     * @return bool
     */
    public function exists(string $resourceId): bool
    {
        return $this->newQuery()->where(
            $this->idName(),
            $resourceId
        )->exists();
    }

    /**
     * @return string
     */
    private function idName(): string
    {
        if ($id = $this->schema->idName()) {
            return $id;
        }

        return $this->model->getRouteKeyName();
    }

    /**
     * @return Model
     */
    private function newInstance(): Model
    {
        $class = $this->schema->model();

        return new $class;
    }

    /**
     * @return EloquentBuilder
     */
    private function newQuery(): EloquentBuilder
    {
        return $this->model->newQuery();
    }
}
