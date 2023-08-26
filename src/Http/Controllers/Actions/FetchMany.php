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

use LaravelJsonApi\Contracts\Http\Actions\FetchMany as FetchManyContract;
use LaravelJsonApi\Core\Responses\DataResponse;
use LaravelJsonApi\Laravel\Http\Requests\JsonApiRequest;

trait FetchMany
{
    /**
     * Fetch zero-to-many JSON:API resources.
     *
     * @param JsonApiRequest $request
     * @param FetchManyContract $action
     * @return DataResponse
     */
    public function index(JsonApiRequest $request, FetchManyContract $action): DataResponse
    {
        return $action
            ->withHooks($this)
            ->execute($request);
    }
}
