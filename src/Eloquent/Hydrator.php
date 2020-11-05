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
use Illuminate\Http\Request;
use LaravelJsonApi\Core\Contracts\Query\QueryParameters as QueryParametersContract;
use LaravelJsonApi\Core\Contracts\Schema\Attribute;
use LaravelJsonApi\Core\Contracts\Store\ResourceBuilder;
use LaravelJsonApi\Core\Query\IncludePaths;
use LaravelJsonApi\Eloquent\Contracts\Fillable;
use RuntimeException;

class Hydrator implements ResourceBuilder
{

    /**
     * @var Schema
     */
    private Schema $schema;

    /**
     * @var Model
     */
    private Model $model;

    /**
     * @var Request|mixed|null
     */
    private $request;

    /**
     * @var IncludePaths|null
     */
    private ?IncludePaths $includePaths = null;

    /**
     * Hydrator constructor.
     *
     * @param Schema $schema
     * @param Model $model
     */
    public function __construct(Schema $schema, Model $model)
    {
        $this->schema = $schema;
        $this->model = $model;
    }

    /**
     * @inheritDoc
     */
    public function using(QueryParametersContract $query): ResourceBuilder
    {
        if ($query instanceof Request) {
            $this->request = $query;
        }

        return $this->with($query->includePaths());
    }

    /**
     * @inheritDoc
     */
    public function with($includePaths): ResourceBuilder
    {
        $this->includePaths = IncludePaths::cast($includePaths);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function store(array $validatedData)
    {
        if (!$this->request) {
            $this->request = \request();
        }

        $model = $this->hydrate($validatedData);

        if ($this->includePaths) {
            $this->schema->loader()
                ->using($model)
                ->loadMissing($this->includePaths);
        }

        return $model;
    }

    /**
     * @param array $validatedData
     * @return Model
     */
    public function hydrate(array $validatedData): Model
    {
        $this->model->getConnection()->transaction(function () use ($validatedData) {
            $this->fillAttributes($validatedData);
            $this->persist();
        });

        return $this->model;
    }

    /**
     * Hydrate JSON API attributes into the model.
     *
     * @param array $validatedData
     * @return void
     */
    private function fillAttributes(array $validatedData): void
    {
        /** @var Attribute $attribute */
        foreach ($this->schema->attributes() as $attribute) {
            if ($attribute instanceof Fillable && array_key_exists($attribute->name(), $validatedData)) {
                $this->fillAttribute($attribute, $validatedData[$attribute->name()]);
            }
        }
    }

    /**
     * Fill an attribute value.
     *
     * @param Fillable $attribute
     * @param $value
     * @return void
     */
    private function fillAttribute(Fillable $attribute, $value): void
    {
        if (true !== $attribute->isReadOnly($this->request)) {
            $attribute->fill($this->model, $value);
        }
    }

    /**
     * Store the model.
     *
     * @return void
     */
    private function persist(): void
    {
        if (true !== $this->model->save()) {
            throw new RuntimeException('Failed to save resource.');
        }
    }
}
