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
use LaravelJsonApi\Core\Support\Str;
use LaravelJsonApi\Laravel\Http\Requests\ResourceQuery;
use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;
use LogicException;

trait AttachRelationship
{

    /**
     * Attach records to a to-many relationship.
     *
     * @param Route $route
     * @param StoreContract $store
     * @return Response|Responsable
     */
    public function attachRelationship(Route $route, StoreContract $store)
    {
        $relation = $route
            ->schema()
            ->relationship($fieldName = $route->fieldName());

        if (!$relation->toMany()) {
            throw new LogicException('Expecting a to-many relation for an attach action.');
        }

        $request = ResourceRequest::forResource(
            $resourceType = $route->resourceType()
        );

        $query = ResourceQuery::queryMany($relation->inverse());

        $model = $route->model();
        $response = null;

        if (method_exists($this, $hook = 'attaching' . Str::classify($fieldName))) {
            $response = $this->{$hook}($model, $request, $query);
        }

        if ($response) {
            return $response;
        }

        $result = $store
            ->modifyToMany($resourceType, $model, $fieldName)
            ->withRequest($query)
            ->attach($request->validatedForRelation());

        if (method_exists($this, $hook = 'attached' . Str::classify($fieldName))) {
            $response = $this->{$hook}($model, $result, $request, $query);
        }

        return $response ?: response('', Response::HTTP_NO_CONTENT);
    }
}
