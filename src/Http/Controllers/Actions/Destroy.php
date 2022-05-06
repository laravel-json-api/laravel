<?php
/*
 * Copyright 2022 Cloud Creativity Limited
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

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use LaravelJsonApi\Contracts\Routing\Route;
use LaravelJsonApi\Contracts\Store\Store as StoreContract;
use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;

trait Destroy
{

    /**
     * Destroy a resource.
     *
     * @param Route $route
     * @param StoreContract $store
     * @return Response|Responsable
     * @throws AuthenticationException|AuthorizationException
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

        abort_unless(
            $acceptable,
            406,
            __("The requested resource is capable of generating only content not acceptable according to the Accept headers sent in the request."),
        );

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
            $check = $route->authorizer()->destroy(
                $request = \request(),
                $model,
            );

            throw_if(false === $check && Auth::guest(), new AuthenticationException());
            throw_if(false === $check, new AuthorizationException());
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
