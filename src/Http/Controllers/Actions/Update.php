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

namespace LaravelJsonApi\Http\Controllers\Actions;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Facades\Route;
use LaravelJsonApi\Core\Resources\DataResponse;
use LaravelJsonApi\Core\Store\Store as ResourceStore;
use LaravelJsonApi\Http\Requests\ResourceQuery;
use LaravelJsonApi\Http\Requests\ResourceRequest;

trait Update
{

    /**
     * Update an existing resource.
     *
     * @param ResourceStore $store
     * @return Responsable
     */
    public function update(ResourceStore $store): Responsable
    {
        $route = Route::current();

        $request = ResourceRequest::forResource(
            $resourceType = $route->parameter('resource_type')
        );

        $modelOrResourceId = $route->parameter(
            $route->parameter('resource_id_name')
        );

        $query = ResourceQuery::queryOne($resourceType);

        $model = $store
            ->update($resourceType, $modelOrResourceId)
            ->using($query)
            ->store($request->validated());

        return new DataResponse($model);
    }
}
