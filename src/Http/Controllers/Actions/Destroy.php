<?php
/*
 * Copyright 2023 Cloud Creativity Limited
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
use LaravelJsonApi\Contracts\Http\Actions\Destroy as DestroyContract;
use LaravelJsonApi\Laravel\Http\Requests\JsonApiRequest;
use Symfony\Component\HttpFoundation\Response;

trait Destroy
{

    /**
     * Destroy a resource.
     *
     * @param JsonApiRequest $request
     * @param DestroyContract $action
     * @return Response|Responsable
     */
    public function destroy(JsonApiRequest $request, DestroyContract $action): Responsable|Response
    {
        return $action
            ->withHooks($this)
            ->execute($request);
    }

}
