<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace LaravelJsonApi\Laravel\Http\Controllers\Actions;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Response;
use LaravelJsonApi\Contracts\Routing\Route;
use LaravelJsonApi\Contracts\Store\Store as StoreContract;
use LaravelJsonApi\Core\Responses\DataResponse;
use LaravelJsonApi\Laravel\Http\Requests\RequestMethod;
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
            $resourceType = $route->resourceType(),
            RequestMethod::UPDATE
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

        return $response ?: DataResponse::make($model)->withQueryParameters($query);
    }
}
