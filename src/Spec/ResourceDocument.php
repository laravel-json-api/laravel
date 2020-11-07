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

namespace LaravelJsonApi\Spec;

class ResourceDocument extends Document
{

    /**
     * @var string
     */
    private string $resourceType;

    /**
     * @var string|null
     */
    private ?string $resourceId;

    /**
     * Document constructor.
     *
     * @param object $document
     * @param string $resourceType
     * @param string|null $resourceId
     */
    public function __construct(object $document, string $resourceType, ?string $resourceId)
    {
        parent::__construct($document);
        $this->resourceType = $resourceType;
        $this->resourceId = $resourceId;
    }


    /**
     * Get the document's expected resource type.
     *
     * @return string
     */
    public function type(): string
    {
        return $this->resourceType;
    }

    /**
     * @return string|null
     */
    public function id(): ?string
    {
        return $this->resourceId;
    }

}
