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
use function json_decode;

class Document
{

    /**
     * @var object
     */
    private object $document;

    /**
     * @var ErrorList
     */
    private ErrorList $errors;

    /**
     * @param $json
     * @return static
     */
    public static function cast($json): self
    {
        if (is_string($json)) {
            return self::fromString($json);
        }

        if (is_object($json)) {
            return new self($json);
        }

        throw new \UnexpectedValueException('Expecting a string or decoded JSON object.');
    }

    /**
     * Create a document from a string.
     *
     * @param string $json
     * @return Document
     */
    public static function fromString(string $json): self
    {
        return new self(json_decode($json, false, JSON_THROW_ON_ERROR));
    }

    /**
     * Document constructor.
     *
     * @param object $document
     */
    public function __construct(object $document)
    {
        $this->document = $document;
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
     * Get a value.
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
