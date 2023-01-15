<?php
/*
 * Copyright 2023 Cloud Creativity Limited
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
use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;

trait UpdateRelationship
{

    /**
     * Update a resource relationship.
     *
     * @param Route $route
     * @param StoreContract $store
     * @return Responsable|Response
     */
    public function updateRelationship(Route $route, StoreContract $store)
    {
        $relation = $route
            ->schema()
            ->relationship($fieldName = $route->fieldName());

        $request = ResourceRequest::forResource(
            $resourceType = $route->resourceType()
        );

        $query = $relation->toOne() ?
            ResourceQuery::queryOne($relation->inverse()) :
            ResourceQuery::queryMany($relation->inverse());

        $model = $route->model();
        $response = null;

        if (method_exists($this, $hook = 'updating' . Str::classify($fieldName))) {
            $response = $this->{$hook}($model, $request, $query);
        }

        if ($response) {
            return $response;
        }

        $data = $request->validatedForRelation();

        if ($relation->toOne()) {
            $result = $store
                ->modifyToOne($resourceType, $model, $fieldName)
                ->withRequest($query)
                ->associate($data);
        } else {
            $result = $store
                ->modifyToMany($resourceType, $model, $fieldName)
                ->withRequest($query)
                ->sync($data);
        }

        if (method_exists($this, $hook = 'updated' . Str::classify($fieldName))) {
            $response = $this->{$hook}($model, $result, $request, $query);
        }

        return $response ?: RelationshipResponse::make(
            $model,
            $fieldName,
            $result
        )->withQueryParameters($query);
    }
}
