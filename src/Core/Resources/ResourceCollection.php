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

use Exception;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use LaravelJsonApi\Core\Document\Links;
use Traversable;

class ResourceCollection implements Responsable, \IteratorAggregate
{

    use Concerns\CreatesResponse;

    /**
     * @var iterable
     */
    public $resources;

    /**
     * ResourceCollection constructor.
     *
     * @param iterable $resources
     */
    public function __construct(iterable $resources)
    {
        $this->resources = $resources;
    }

    /**
     * @return array
     */
    public function meta(): array
    {
        return [];
    }

    /**
     * @return Links
     */
    public function links(): Links
    {
        return new Links();
    }

    /**
     * @inheritDoc
     */
    public function getIterator()
    {
        yield from $this->resources;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function toResponse($request)
    {
        return (new ResourceCollectionResponse($this))->toResponse($request);
    }

}
