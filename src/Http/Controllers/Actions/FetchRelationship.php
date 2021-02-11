<?php
/*
 * Copyright 2021 Cloud Creativity Limited
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

namespace LaravelJsonApi\Laravel\Http\Controllers\Actions;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Response;
use LaravelJsonApi\Contracts\Routing\Route;
use LaravelJsonApi\Contracts\Store\Store as StoreContract;
use LaravelJsonApi\Core\Responses\RelationshipResponse;
use LaravelJsonApi\Core\Support\Str;
use LaravelJsonApi\Laravel\Http\Requests\ResourceQuery;

trait FetchRelationship
{

    /**
     * Fetch the resource identifier(s) for a JSON API relationship.
     *
     * @param Route $route
     * @param StoreContract $store
     * @return Responsable|Response
     */
    public function showRelationship(Route $route, StoreContract $store)
    {
        $relation = $route->schema()->relationship(
            $fieldName = $route->fieldName()
        );

        $request = $relation->toOne() ?
            ResourceQuery::queryOne($relation->inverse()) :
            ResourceQuery::queryMany($relation->inverse());

        $model = $route->model();

        if (method_exists($this, $hook = 'reading' . Str::classify($fieldName))) {
            $this->{$hook}($model, $request);
        }

        if ($relation->toOne()) {
            $data = $store->queryToOne(
                $route->resourceType(),
                $model,
                $relation->name()
            )->withRequest($request)->first();
        } else {
            $data = $store->queryToMany(
                $route->resourceType(),
                $model,
                $relation->name()
            )->withRequest($request)->getOrPaginate($request->page());
        }

        $response = null;

        if (method_exists($this, $hook = 'read' . Str::classify($fieldName))) {
            $response = $this->{$hook}($model, $data, $request);
        }

        return $response ?: new RelationshipResponse(
            $model,
            $relation->name(),
            $data
        );
    }
}
