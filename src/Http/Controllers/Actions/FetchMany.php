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
use LaravelJsonApi\Laravel\Http\Requests\ResourceQuery;

trait FetchMany
{

    /**
     * Fetch zero to many JSON API resources.
     *
     * @param Route $route
     * @param StoreContract $store
     * @return Responsable|Response
     */
    public function index(Route $route, StoreContract $store)
    {
        $request = ResourceQuery::queryMany(
            $resourceType = $route->resourceType()
        );

        $response = null;

        if (method_exists($this, 'searching')) {
            $response = $this->searching($request);
        }

        if ($response) {
            return $response;
        }

        $data = $store
            ->queryAll($resourceType)
            ->withRequest($request)
            ->firstOrPaginate($request->page());

        if (method_exists($this, 'searched')) {
            $response = $this->searched($data, $request);
        }

        return $response ?: DataResponse::make($data)->withQueryParameters($request);
    }
}
