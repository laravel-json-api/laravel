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

use LaravelJsonApi\Contracts\Http\Actions\FetchOne as FetchOneContract;
use LaravelJsonApi\Core\Responses\DataResponse;
use LaravelJsonApi\Laravel\Http\Requests\JsonApiRequest;

trait FetchOne
{
    /**
     * Fetch zero to one JSON:API resource by id.
     *
     * @param JsonApiRequest $request
     * @param FetchOneContract $action
     * @return DataResponse
     */
    public function show(JsonApiRequest $request, FetchOneContract $action): DataResponse
    {
        return $action
            ->withHooks($this)
            ->execute($request);
    }
}
