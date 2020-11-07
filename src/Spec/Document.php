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

use LaravelJsonApi\Core\Document\ErrorList;

class Document
{

    /**
     * @var object
     */
    private object $document;

    /**
     * @var string
     */
    private string $resourceType;

    /**
     * @var string|null
     */
    private ?string $relation;

    /**
     * @var ErrorList
     */
    private ErrorList $errors;

    /**
     * Document constructor.
     *
     * @param object $document
     * @param string $resourceType
     * @param string|null $relation
     */
    public function __construct(object $document, string $resourceType, string $relation = null)
    {
        $this->document = $document;
        $this->resourceType = $resourceType;
        $this->relation = $relation;
        $this->errors = new ErrorList();
    }

    /**
     * @param $name
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->document->{$name});
    }

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->document->{$name};
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
     * Get the relation that the document represents.
     *
     * @return string|null
     */
    public function relation(): ?string
    {
        return $this->relation;
    }

    /**
     * @return bool
     */
    public function isRelation(): bool
    {
        return is_string($this->relation);
    }

    /**
     * @return bool
     */
    public function isNotRelation(): bool
    {
        return !$this->isRelation();
    }

    /**
     * Get a value from the document using dot notation.
     *
     * @param string $path
     * @param mixed|null $default
     * @return mixed
     */
    public function get(string $path, $default = null)
    {
        return data_get($this->document, $path, $default);
    }

    /**
     * @return ErrorList
     */
    public function errors(): ErrorList
    {
        return $this->errors;
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return $this->errors->isEmpty();
    }

    /**
     * @return bool
     */
    public function invalid(): bool
    {
        return !$this->valid();
    }

    /**
     * @return object
     */
    public function toBase(): object
    {
        return $this->document;
    }

}
