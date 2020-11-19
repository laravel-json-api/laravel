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

namespace LaravelJsonApi\Laravel\Http\Controllers\Actions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use LaravelJsonApi\Contracts\Routing\Route;
use LaravelJsonApi\Contracts\Store\Store as StoreContract;

trait Destroy
{

    /**
     * Destroy a resource.
     *
     * @param Route $route
     * @param StoreContract $store
     * @return Response
     */
    public function destroy(Route $route, StoreContract $store): Response
    {
        $model = $route->model();

        if (method_exists($this, 'authorizeDestroy')) {
            $this->authorizeDestroy(Auth::user(), $model);
        } else if (true !== Gate::check('delete', $model)) {
            if (Auth::guest()) {
                throw new AuthenticationException();
            }

            throw new AuthorizationException();
        }

        $store->delete($route->resourceType(), $model);

        return response(null, Response::HTTP_NO_CONTENT);
    }

}
