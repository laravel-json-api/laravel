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

namespace LaravelJsonApi\Contracts\Encoder;

use LaravelJsonApi\Core\Resources\JsonApiResource;

interface Encoder
{
    /**
     * @param $includePaths
     * @return $this
     */
    public function withIncludePaths($includePaths): self;

    /**
     * @param $fieldSets
     * @return $this
     */
    public function withFieldSets($fieldSets): self;

    /**
     * Create a compound document with a resource as the top-level data member.
     *
     * @param JsonApiResource|null $resource
     * @return JsonApiDocument
     */
    public function withResource(?JsonApiResource $resource): JsonApiDocument;

    /**
     * @param iterable $resources
     * @return JsonApiDocument
     */
    public function withResources(iterable $resources): JsonApiDocument;

    /**
     * Create a compound document for a resource or resources.
     *
     * @param $data
     * @return JsonApiDocument
     */
    public function withData($data): JsonApiDocument;

    /**
     * Create a compound document for a relationship identifier or identifiers.
     *
     * @param JsonApiResource $resource
     * @param string $fieldName
     * @param JsonApiResource|iterable|null $identifiers
     * @return JsonApiDocument
     */
    public function withIdentifiers(JsonApiResource $resource, string $fieldName, $identifiers): JsonApiDocument;
}
