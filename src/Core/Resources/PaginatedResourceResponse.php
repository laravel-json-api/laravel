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

use Illuminate\Contracts\Pagination\Paginator;
use InvalidArgumentException;
use LaravelJsonApi\Core\Contracts\Pagination\Page as PageContract;
use LaravelJsonApi\Core\Document\Links;
use LaravelJsonApi\Core\Json\Hash;
use LaravelJsonApi\Core\Pagination\Page;

class PaginatedResourceResponse extends ResourceCollectionResponse
{

    /**
     * @var PageContract
     */
    private PageContract $page;

    /**
     * PaginatedResourceResponse constructor.
     *
     * @param PageContract|Paginator|ResourceCollection $resources
     */
    public function __construct($resources)
    {
        if ($resources instanceof PageContract) {
            $this->page = $resources;
            $resources = new ResourceCollection($resources);
        } else if ($resources instanceof Paginator) {
            $this->page = new Page($resources);
            $resources = new ResourceCollection($resources);
        } else if ($resources instanceof ResourceCollection && $resources->resources instanceof PageContract) {
            $this->page = $resources->resources;
        } else {
            throw new InvalidArgumentException('Expecting a page or a resource collection that contains a page.');
        }

        parent::__construct($resources);
    }

    /**
     * @return Hash
     */
    public function meta(): Hash
    {
        return (new Hash($this->page->meta()))->merge(
            parent::meta()
        );
    }

    /**
     * @return Links
     */
    public function links(): Links
    {
        return $this->page->links()->merge(
            parent::links()
        );
    }

}
