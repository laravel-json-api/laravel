<?php
/*
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
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use LaravelJsonApi\Contracts\Pagination\Page;
use LaravelJsonApi\Core\Facades\JsonApi;
use LaravelJsonApi\Core\Resources\JsonApiResource;
use function is_null;

class RelationshipResponse implements Responsable
{

    use Concerns\IsResponsable;

    /**
     * @var object
     */
    private object $resource;

    /**
     * @var string
     */
    private string $fieldName;

    /**
     * @var Page|Model|iterable|null
     */
    private $related;

    /**
     * RelationshipResponse constructor.
     *
     * @param object $resource
     * @param string $fieldName
     * @param Page|Model|iterable|null $related
     */
    public function __construct(object $resource, string $fieldName, $related)
    {
        $this->resource = $resource;
        $this->fieldName = $fieldName;
        $this->related = $related;
    }

    /**
     * @param Request $request
     * @return ResourceCollectionResponse|ResourceResponse
     */
    public function prepareResponse($request): Responsable
    {
        return $this
            ->prepareDataResponse($request)
            ->withJsonApi($this->jsonApi())
            ->withMeta($this->meta)
            ->withLinks($this->links)
            ->withEncodeOptions($this->encodeOptions)
            ->withHeaders($this->headers);
    }

    /**
     * @inheritDoc
     */
    public function toResponse($request)
    {
        return $this
            ->prepareResponse($request)
            ->toResponse($request);
    }

    /**
     * Convert the data member to a response class.
     *
     * @param $request
     * @return ResourceIdentifierResponse|ResourceIdentifierCollectionResponse
     */
    private function prepareDataResponse($request)
    {
        $resolver = JsonApi::server()->resources();
        $resource = $resolver->resolve($this->resource);

        if (is_null($this->related)) {
            return new ResourceIdentifierResponse(
                $resource,
                $this->fieldName,
                null
            );
        }

        $parsed = $resolver->resolve($this->related);

        if ($parsed instanceof JsonApiResource) {
            return new ResourceIdentifierResponse(
                $resource,
                $this->fieldName,
                $parsed
            );
        }

        return new ResourceIdentifierCollectionResponse(
            $resource,
            $this->fieldName,
            $parsed
        );
    }

}
