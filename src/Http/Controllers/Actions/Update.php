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
use LaravelJsonApi\Core\Responses\DataResponse;
use LaravelJsonApi\Laravel\Http\Requests\ResourceQuery;
use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;

trait Update
{

    /**
     * Update an existing resource.
     *
     * @param Route $route
     * @param StoreContract $store
     * @return Responsable|Response
     */
    public function update(Route $route, StoreContract $store)
    {
        $request = ResourceRequest::forResource(
            $resourceType = $route->resourceType()
        );

        $query = ResourceQuery::queryOne($resourceType);

        $model = $route->model();
        $response = null;

        if (method_exists($this, 'saving')) {
            $response = $this->saving($model, $request, $query);
        }

        if (!$response && method_exists($this, 'updating')) {
            $response = $this->updating($model, $request, $query);
        }

        if ($response) {
            return $response;
        }

        $model = $store
            ->update($resourceType, $model)
            ->withRequest($query)
            ->store($request->validated());

        if (method_exists($this, 'updated')) {
            $response = $this->updated($model, $request, $query);
        }

        if (!$response && method_exists($this, 'saved')) {
            $response = $this->saved($model, $request, $query);
        }

        return $response ?: (new DataResponse($model))->withRequest($query);
    }
}
