<?php
/*
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

namespace LaravelJsonApi\Laravel\Http\Controllers\Actions;

use Illuminate\Contracts\Support\Responsable;
use LaravelJsonApi\Contracts\Routing\Route;
use LaravelJsonApi\Contracts\Store\Store as StoreContract;
use LaravelJsonApi\Core\Responses\DataResponse;
use LaravelJsonApi\Laravel\Http\Requests\ResourceQuery;

trait FetchRelated
{

    /**
     * Fetch the related resource(s) for a JSON API relationship.
     *
     * @param Route $route
     * @param StoreContract $store
     * @return Responsable
     */
    public function readRelated(Route $route, StoreContract $store): Responsable
    {
        $relation = $route
            ->schema()
            ->relationship($route->fieldName());

        if ($relation->toOne()) {
            $request = ResourceQuery::queryOne($relation->inverse());
            $data = $store->queryToOne(
                $route->resourceType(),
                $route->modelOrResourceId(),
                $relation->name()
            )->using($request)->first();
        } else {
            $request = ResourceQuery::queryMany($relation->inverse());
            $data = $store->queryToMany(
                $route->resourceType(),
                $route->modelOrResourceId(),
                $relation->name()
            )->using($request)->getOrPaginate($request->page());
        }

        return new DataResponse($data);
    }
}
