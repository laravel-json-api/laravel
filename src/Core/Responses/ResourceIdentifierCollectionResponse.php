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

namespace LaravelJsonApi\Core\Responses;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use LaravelJsonApi\Core\Facades\JsonApi;
use LaravelJsonApi\Core\Resources\JsonApiResource;

class ResourceIdentifierCollectionResponse implements Responsable
{

    use Concerns\IsResponsable;

    /**
     * @var JsonApiResource
     */
    private JsonApiResource $resource;

    /**
     * @var string
     */
    private string $fieldName;

    /**
     * @var iterable
     */
    private iterable $related;

    /**
     * @var bool
     */
    private bool $created = false;

    /**
     * ResourceIdentifierResponse constructor.
     *
     * @param JsonApiResource $resource
     * @param string $fieldName
     * @param iterable $related
     */
    public function __construct(JsonApiResource $resource, string $fieldName, iterable $related)
    {
        $this->resource = $resource;
        $this->fieldName = $fieldName;
        $this->related = $related;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function toResponse($request)
    {
        $document = JsonApi::server()->encoder()
            ->withIncludePaths($this->includePaths($request))
            ->withFieldSets($this->fieldSets($request))
            ->withIdentifiers($this->resource, $this->fieldName, $this->related)
            ->withJsonApi($this->jsonApi())
            ->withMeta($this->meta)
            ->withLinks($this->links)
            ->toJson($this->encodeOptions);

        return response(
            $document,
            Response::HTTP_OK,
            $this->headers()
        );
    }

    /**
     * @return array
     */
    protected function headers(): array
    {
        return \collect(['Content-Type' => 'application/vnd.api+json'])
            ->merge($this->headers ?: [])
            ->all();
    }

}
