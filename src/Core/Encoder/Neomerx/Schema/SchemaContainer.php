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

use LaravelJsonApi\Core\Contracts\Resources\Container;
use LaravelJsonApi\Core\Encoder\Neomerx\Mapper;
use LaravelJsonApi\Core\Resources\JsonApiResource;
use LogicException;
use Neomerx\JsonApi\Contracts\Schema\SchemaContainerInterface;
use Neomerx\JsonApi\Contracts\Schema\SchemaInterface;

/**
 * Class SchemaContainer
 *
 * @internal
 */
final class SchemaContainer implements SchemaContainerInterface
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
     * @var SchemaFields
     */
    private SchemaFields $fields;

    /**
     * @var array
     */
    private array $schemas;

    /**
     * SchemaContainer constructor.
     *
     * @param Container $container
     * @param Mapper $mapper
     * @param SchemaFields $fields
     */
    public function __construct(
        Container $container,
        Mapper $mapper,
        SchemaFields $fields
    ) {
        $this->container = $container;
        $this->mapper = $mapper;
        $this->fields = $fields;
        $this->schemas = [];
    }

    /**
     * @param JsonApiResource $resourceObject
     * @return SchemaInterface
     */
    public function getSchema($resourceObject): SchemaInterface
    {
        if (!$resourceObject instanceof JsonApiResource) {
            throw new LogicException('Expecting a resource object.');
        }

        $type = $resourceObject->type();

        if (isset($this->schemas[$type])) {
            return $this->schemas[$type];
        }

        return $this->schemas[$type] = $this->createSchema($type);
    }

    /**
     * @param mixed $resourceObject
     * @return bool
     */
    public function hasSchema($resourceObject): bool
    {
        return $resourceObject instanceof JsonApiResource;
    }

    /**
     * @param string $type
     * @return SchemaInterface
     */
    private function createSchema(string $type): SchemaInterface
    {
        return new Schema($this->container, $this->mapper, $this->fields, $type);
    }

}
