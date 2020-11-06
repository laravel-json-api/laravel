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
     * @return CompoundDocument
     */
    public function withResource(?JsonApiResource $resource): CompoundDocument;

    /**
     * @param iterable $resources
     * @return CompoundDocument
     */
    public function withResources(iterable $resources): CompoundDocument;

    /**
     * Create a compound document.
     *
     * @param $data
     * @return CompoundDocument
     */
    public function withData($data): CompoundDocument;
}
