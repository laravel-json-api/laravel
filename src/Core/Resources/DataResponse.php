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

namespace LaravelJsonApi\Core\Resources;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use LaravelJsonApi\Core\Contracts\Pagination\Page;
use LaravelJsonApi\Core\Facades\JsonApi;
use function is_null;

class DataResponse implements Responsable
{

    use Concerns\CreatesResponse;

    /**
     * @var Page|Model|iterable|null
     */
    private $value;

    /**
     * DocumentResponse constructor.
     *
     * @param Page|Model|iterable|null $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @param Request $request
     * @return ResourceCollectionResponse|ResourceResponse
     */
    public function prepareResponse($request)
    {
        if ($this->value instanceof Page) {
            return new PaginatedResourceResponse($this->value);
        }

        if (is_null($this->value)) {
            return new ResourceResponse(null);
        }

        $parsed = JsonApi::server()
            ->resources()
            ->resolve($this->value);

        if ($parsed instanceof JsonApiResource) {
            return $parsed->prepareResponse($request);
        }

        return (new ResourceCollection($parsed))->prepareResponse($request);
    }

    /**
     * @inheritDoc
     */
    public function toResponse($request)
    {
        return $this
            ->prepareResponse($request)
            ->withMeta($this->meta)
            ->withLinks($this->links)
            ->withEncodeOptions($this->encodeOptions)
            ->withHeaders((array) $this->headers)
            ->toResponse($request);
    }

}
