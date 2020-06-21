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

namespace LaravelJsonApi\Encoder;

use LaravelJsonApi\Core\Contracts\Resources\Container;
use LaravelJsonApi\Core\Query\FieldSets;
use LaravelJsonApi\Core\Query\IncludePaths;
use LaravelJsonApi\Core\Resources\JsonApiResource;
use LaravelJsonApi\Encoder\Neomerx\Mapper;
use LaravelJsonApi\Encoder\Neomerx\Schema\SchemaContainer;
use LaravelJsonApi\Encoder\Neomerx\Schema\SchemaFields;
use Neomerx\JsonApi\Factories\Factory;

class Encoder
{

    /**
     * @var Container
     */
    private $container;

    /**
     * @var Factory
     */
    private $factory;

    /**
     * @var Mapper
     */
    private $mapper;

    /**
     * @var IncludePaths|null
     */
    private $includePaths;

    /**
     * @var FieldSets|null
     */
    private $fieldSets;

    /**
     * Encoder constructor.
     *
     * @param Container $container
     * @param Factory $factory
     * @param Mapper $mapper
     */
    public function __construct(Container $container, Factory $factory, Mapper $mapper)
    {
        $this->container = $container;
        $this->factory = $factory;
        $this->mapper = $mapper;
    }

    /**
     * @param $includePaths
     * @return $this
     */
    public function withIncludePaths($includePaths): self
    {
        $this->includePaths = IncludePaths::cast($includePaths);

        return $this;
    }

    /**
     * @param $fieldSets
     * @return $this
     */
    public function withFieldSets($fieldSets): self
    {
        $this->fieldSets = FieldSets::cast($fieldSets);

        return $this;
    }

    /**
     * Create a compound document with a resource as the top-level data member.
     *
     * @param JsonApiResource|null $resource
     * @return CompoundDocument
     */
    public function withResource(?JsonApiResource $resource): CompoundDocument
    {
        return $this->withData($resource);
    }

    /**
     * @param iterable $resources
     * @return CompoundDocument
     */
    public function withResources(iterable $resources): CompoundDocument
    {
        return $this->withData(
            $this->container->cursor($resources)
        );
    }

    /**
     * Create a compound document.
     *
     * @param $data
     * @return CompoundDocument
     */
    public function withData($data): CompoundDocument
    {
        return new CompoundDocument($this->encoder(), $this->mapper, $data);
    }

    /**
     * Create a new encoder instance.
     *
     * @return Neomerx\Encoder
     */
    private function encoder(): Neomerx\Encoder
    {
        $schemas = new SchemaContainer(
            $this->container,
            $this->mapper,
            new SchemaFields($this->includePaths ?: new IncludePaths(), $this->fieldSets ?: new FieldSets())
        );

        return new Neomerx\Encoder($this->factory, $schemas);
    }
}
