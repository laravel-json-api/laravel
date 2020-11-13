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

use LaravelJsonApi\Encoder\Neomerx\Encoder\Encoder as ExtendedEncoder;

class RelationshipDocument extends Document
{

    /**
     * @var object
     */
    private object $resource;

    /**
     * @var string
     */
    private string $fieldName;

    /**
     * @var mixed
     */
    private $data;

    /**
     * RelationshipDocument constructor.
     *
     * @param ExtendedEncoder $encoder
     * @param Mapper $mapper
     * @param object $resource
     * @param string $fieldName
     * @param object|iterable|null $data
     */
    public function __construct(
        ExtendedEncoder $encoder,
        Mapper $mapper,
        object $resource,
        string $fieldName,
        $data
    ) {
        parent::__construct($encoder, $mapper);
        $this->resource = $resource;
        $this->fieldName = $fieldName;
        $this->data = $data;
    }

    /**
     * @inheritDoc
     */
    protected function serialize(): array
    {
        return $this
            ->encoder()
            ->withRelationshipSelfLink($this->resource, $this->fieldName)
            ->withRelationshipRelatedLink($this->resource, $this->fieldName)
            ->serializeIdentifiers($this->data);
    }

    /**
     * @inheritDoc
     */
    protected function encode(): string
    {
        return $this
            ->encoder()
            ->withRelationshipSelfLink($this->resource, $this->fieldName)
            ->withRelationshipRelatedLink($this->resource, $this->fieldName)
            ->encodeIdentifiers($this->data);
    }

}
