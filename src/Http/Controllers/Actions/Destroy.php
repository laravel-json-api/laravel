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

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Access\Response as AuthResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use LaravelJsonApi\Contracts\Routing\Route;
use LaravelJsonApi\Contracts\Store\Store as StoreContract;
use LaravelJsonApi\Laravel\Exceptions\HttpNotAcceptableException;
use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;

trait Destroy
{

    /**
     * Destroy a resource.
     *
     * @param Route $route
     * @param StoreContract $store
     * @return Response|Responsable
     * @throws AuthenticationException|AuthorizationException|HttpNotAcceptableException
     */
    public function destroy(Route $route, StoreContract $store)
    {
        /**
         * As we do not have a query request class for a delete request,
         * we need to manually check that the request Accept header
         * is the JSON:API media type.
         */
        $acceptable = false;

        foreach (request()->getAcceptableContentTypes() as $contentType) {
            if ($contentType === ResourceRequest::JSON_API_MEDIA_TYPE) {
                $acceptable = true;
                break;
            }
        }

        throw_unless($acceptable, new HttpNotAcceptableException());

        $request = ResourceRequest::forResourceIfExists(
            $resourceType = $route->resourceType()
        );

        $model = $route->model();

        /**
         * The resource request class is optional for deleting,
         * as delete validation is optional. However, if we do not have
         * a resource request then the action will not have been authorized.
         * So we need to trigger authorization in this case.
         */
        if (!$request) {
            $result = $route->authorizer()->destroy(
                $request = \request(),
                $model,
            );

            if ($result instanceof AuthResponse) {
                try {
                    $result->authorize();
                } catch (AuthorizationException $ex) {
                    if (!$ex->hasStatus()) {
                        throw_if(Auth::guest(), new AuthenticationException());
                    }
                    throw $ex;
                }
            }

            throw_if(false === $result && Auth::guest(), new AuthenticationException());
            throw_if(false === $result, new AuthorizationException());
        }

        $response = null;

        if (method_exists($this, 'deleting')) {
            $response = $this->deleting($model, $request);
        }

        if ($response) {
            return $response;
        }

        $store->delete(
            $resourceType,
            $route->modelOrResourceId()
        );

        if (method_exists($this, 'deleted')) {
            $response = $this->deleted($model, $request);
        }

        return $response ?: response(null, Response::HTTP_NO_CONTENT);
    }

}
