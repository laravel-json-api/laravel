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

namespace LaravelJsonApi\Encoder\Neomerx\Schema;

use LaravelJsonApi\Core\Contracts\Resources\Container;
use LaravelJsonApi\Core\Document\ResourceIdentifier;
use LaravelJsonApi\Core\Json\Hash;
use LaravelJsonApi\Core\Resources\JsonApiResource;
use LaravelJsonApi\Core\Resources\Relation as ResourceRelation;
use LaravelJsonApi\Encoder\Neomerx\Mapper;
use Neomerx\JsonApi\Contracts\Schema\ContextInterface;
use Neomerx\JsonApi\Contracts\Schema\IdentifierInterface;
use Neomerx\JsonApi\Contracts\Schema\SchemaInterface;
use function is_null;

/**
 * Class Relation
 *
 * @package LaravelJsonApi\Encoder\Neomerx
 * @internal
 */
final class Relation
{

    /**
     * @var Container
     */
    private $container;

    /**
     * @var Mapper
     */
    private $mapper;

    /**
     * @var ResourceRelation
     */
    private $relation;

    /**
     * @var SchemaFields
     */
    private $fields;

    /**
     * @var ContextInterface
     */
    private $context;

    /**
     * @var string
     */
    private $fieldName;

    /**
     * Relation constructor.
     *
     * @param Container $container
     * @param Mapper $mapper
     * @param ResourceRelation $object
     * @param SchemaFields $fields
     * @param ContextInterface $context
     * @param string $fieldName
     */
    public function __construct(
        Container $container,
        Mapper $mapper,
        ResourceRelation $object,
        SchemaFields $fields,
        ContextInterface $context,
        string $fieldName
    ) {
        $this->container = $container;
        $this->mapper = $mapper;
        $this->relation = $object;
        $this->fields = $fields;
        $this->context = $context;
        $this->fieldName = $fieldName;
    }

    /**
     * @return ResourceObject|IdentifierInterface|iterable|null
     */
    public function data()
    {
        $data = $this->relation->data();

        if ($data instanceof JsonApiResource || is_null($data)) {
            return $data;
        }

        if ($data instanceof ResourceIdentifier) {
            return $this->mapper->identifier($data);
        }

        return $this->container->resolve($data);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $relation = [];
        $links = $this->relation->links();
        $meta = new Hash($this->relation->meta() ?: []);

        if ($this->willShowData()) {
            $relation[SchemaInterface::RELATIONSHIP_DATA] = $this->data();
        }

        if ($links->isNotEmpty()) {
            $relation[SchemaInterface::RELATIONSHIP_LINKS] = $this->mapper->allLinks(
                $this->relation->links()
            );
        }

        if ($meta->isNotEmpty()) {
            $relation[SchemaInterface::RELATIONSHIP_META] = $meta;
        }

        return $relation;
    }

    /**
     * @return bool
     */
    private function willShowData(): bool
    {
        if ($this->relation->showData()) {
            return true;
        }

        return $this->fields->isRelationshipRequested(
            $this->context->getPosition()->getPath(),
            $this->fieldName
        );
    }

}
