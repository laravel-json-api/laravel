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

namespace LaravelJsonApi\Core\Encoder\Neomerx\Schema;

use IteratorAggregate;
use LaravelJsonApi\Core\Contracts\Resources\Skippable;
use LaravelJsonApi\Core\Resources\ConditionalAttrs;
use LaravelJsonApi\Core\Resources\JsonApiResource;
use Neomerx\JsonApi\Contracts\Schema\ContextInterface;

/**
 * Class Attrs
 *
 * @internal
 */
final class Attrs implements IteratorAggregate
{

    /**
     * @var JsonApiResource
     */
    private JsonApiResource $resource;

    /**
     * @var ContextInterface
     */
    private ContextInterface $context;

    /**
     * Attrs constructor.
     *
     * @param JsonApiResource $resource
     * @param ContextInterface $context
     */
    public function __construct(JsonApiResource $resource, ContextInterface $context)
    {
        $this->resource = $resource;
        $this->context = $context;
    }

    /**
     * @inheritDoc
     */
    public function getIterator()
    {
        foreach ($this->resource->attributes() as $key => $value) {
            if ($value instanceof Skippable && true === $value->skip()) {
                continue;
            }

            if ($value instanceof ConditionalAttrs) {
                yield from $value;
                continue;
            }

            yield $key => $value;
        }
    }

}
