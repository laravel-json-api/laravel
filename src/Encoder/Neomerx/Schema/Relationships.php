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

namespace LaravelJsonApi\Encoder\Neomerx\Schema;

use IteratorAggregate;
use LaravelJsonApi\Contracts\Resources\Container;
use LaravelJsonApi\Encoder\Neomerx\Mapper;
use LaravelJsonApi\Core\Resources\JsonApiResource;
use Neomerx\JsonApi\Contracts\Schema\ContextInterface;

/**
 * Class Relationships
 *
 * @internal
 */
final class Relationships implements IteratorAggregate
{

    /**
     * @var Container
     */
    private Container $container;

    /**
     * @var Mapper
     */
    private Mapper $mapper;

    /**
     * @var JsonApiResource
     */
    private JsonApiResource $resource;

    /**
     * @var SchemaFields
     */
    private SchemaFields $fields;

    /**
     * @var ContextInterface
     */
    private ContextInterface $context;

    /**
     * Relationships constructor.
     *
     * @param Container $container
     * @param Mapper $mapper
     * @param JsonApiResource $resource
     * @param SchemaFields $fields
     * @param ContextInterface $context
     */
    public function __construct(
        Container $container,
        Mapper $mapper,
        JsonApiResource $resource,
        SchemaFields $fields,
        ContextInterface $context
    ) {
        $this->container = $container;
        $this->mapper = $mapper;
        $this->resource = $resource;
        $this->fields = $fields;
        $this->context = $context;
    }

    /**
     * @inheritDoc
     */
    public function getIterator()
    {
        /** @var \LaravelJsonApi\Core\Resources\Relation $relation */
        foreach ($this->resource->relationships() as $relation) {
            $fieldName = $relation->fieldName();

            if ($this->fields->isFieldRequested($this->resource->type(), $fieldName)) {
                yield $fieldName => (new Relation(
                    $this->container,
                    $this->mapper,
                    $relation,
                    $this->fields,
                    $this->context,
                    $fieldName
                ))->toArray();
            }
        }
    }

}
