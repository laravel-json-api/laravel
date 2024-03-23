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
        $response = null;

        if (method_exists($this, $hook = 'reading' . Str::classify($fieldName))) {
            $response = $this->{$hook}($model, $request);
        }

        if ($response) {
            return $response;
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

        if (method_exists($this, $hook = 'read' . Str::classify($fieldName))) {
            $response = $this->{$hook}($model, $data, $request);
        }

        return $response ?: RelationshipResponse::make(
            $model,
            $relation->name(),
            $data
        )->withQueryParameters($request);
    }
}
