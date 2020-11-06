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

namespace LaravelJsonApi\Encoder\Neomerx;

use LaravelJsonApi\Contracts\Encoder\Encoder as EncoderContract;
use LaravelJsonApi\Contracts\Resources\Container;
use LaravelJsonApi\Encoder\Neomerx\Mapper;
use LaravelJsonApi\Encoder\Neomerx\Schema\SchemaContainer;
use LaravelJsonApi\Encoder\Neomerx\Schema\SchemaFields;
use LaravelJsonApi\Core\Query\FieldSets;
use LaravelJsonApi\Core\Query\IncludePaths;
use LaravelJsonApi\Core\Resources\JsonApiResource;
use Neomerx\JsonApi\Contracts\Factories\FactoryInterface;

class Encoder implements EncoderContract
{

    /**
     * @var Container
     */
    private Container $resources;

    /**
     * @var FactoryInterface
     */
    private FactoryInterface $factory;

    /**
     * @var Mapper
     */
    private Mapper $mapper;

    /**
     * @var IncludePaths|null
     */
    private ?IncludePaths $includePaths = null;

    /**
     * @var FieldSets|null
     */
    private ?FieldSets $fieldSets = null;

    /**
     * Encoder constructor.
     *
     * @param Container $container
     * @param FactoryInterface $factory
     * @param Mapper $mapper
     */
    public function __construct(Container $container, FactoryInterface $factory, Mapper $mapper)
    {
        $this->resources = $container;
        $this->factory = $factory;
        $this->mapper = $mapper;
    }

    /**
     * @inheritDoc
     */
    public function withIncludePaths($includePaths): self
    {
        $this->includePaths = IncludePaths::cast($includePaths);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withFieldSets($fieldSets): self
    {
        $this->fieldSets = FieldSets::cast($fieldSets);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withResource(?JsonApiResource $resource): CompoundDocument
    {
        return $this->withData($resource);
    }

    /**
     * @inheritDoc
     */
    public function withResources(iterable $resources): CompoundDocument
    {
        return $this->withData(
            $this->resources->cursor($resources)
        );
    }

    /**
     * @inheritDoc
     */
    public function withData($data): CompoundDocument
    {
        return new CompoundDocument($this->encoder(), $this->mapper, $data);
    }

    /**
     * Create a new encoder instance.
     *
     * @return \LaravelJsonApi\Encoder\Neomerx\Encoder\Encoder
     */
    private function encoder(): Encoder\Encoder
    {
        $schemas = new SchemaContainer(
            $this->resources,
            $this->mapper,
            new SchemaFields($this->includePaths ?: new IncludePaths(), $this->fieldSets ?: new FieldSets())
        );

        $encoder = new Encoder\Encoder($this->factory, $schemas);

        if ($this->includePaths) {
            $encoder->withIncludedPaths($this->includePaths->toArray());
        }

        if ($this->fieldSets) {
            $encoder->withFieldSets($this->fieldSets->toArray());
        }

        return $encoder;
    }
}
