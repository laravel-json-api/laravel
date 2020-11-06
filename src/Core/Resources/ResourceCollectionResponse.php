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
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use LaravelJsonApi\Core\Document\Links;
use LaravelJsonApi\Core\Json\Hash;
use LaravelJsonApi\Core\Facades\JsonApi;

class ResourceCollectionResponse implements Responsable
{

    use Concerns\CreatesResponse;

    /**
     * @var ResourceCollection
     */
    private ResourceCollection $resources;

    /**
     * ResourceCollectionResponse constructor.
     *
     * @param ResourceCollection $resources
     */
    public function __construct(ResourceCollection $resources)
    {
        $this->resources = $resources;
    }

    /**
     * @return Links
     */
    public function links(): Links
    {
        return $this->resources->links()->merge(
            $this->links ?: new Links()
        );
    }

    /**
     * @return Hash
     */
    public function meta(): Hash
    {
        return (new Hash($this->resources->meta()))->merge(
            $this->meta ?: []
        );
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
            ->withResources($this->resources)
            ->withMeta($this->meta())
            ->withLinks($this->links())
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
