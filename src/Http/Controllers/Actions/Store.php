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

use LaravelJsonApi\Contracts\Http\Actions\Store as StoreContract;
use LaravelJsonApi\Core\Responses\DataResponse;
use LaravelJsonApi\Laravel\Http\Requests\JsonApiRequest;

trait Store
{
    /**
     * Create a new resource.
     *
     * @param JsonApiRequest $request
     * @param StoreContract $action
     * @return DataResponse
     */
    public function store(JsonApiRequest $request, StoreContract $action): DataResponse
    {
        return $action
            ->withHooks($this)
            ->execute($request);
    }
}
