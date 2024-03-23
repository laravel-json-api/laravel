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
