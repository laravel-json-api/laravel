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

namespace LaravelJsonApi\Core\Document;

use LaravelJsonApi\Core\Contracts\Serializable;
use LogicException;
use function array_filter;
use function is_array;
use function is_null;

class ErrorSource implements Serializable
{

    use Concerns\Serializable;

    /**
     * @var string|null
     */
    private ?string $pointer;

    /**
     * @var string|null
     */
    private ?string $parameter;

    /**
     * @param ErrorSource|array|null $value
     * @return ErrorSource
     */
    public static function cast($value): self
    {
        if (is_null($value)) {
            return new self();
        }

        if ($value instanceof self) {
            return $value;
        }

        if (is_array($value)) {
            return self::fromArray($value);
        }

        throw new LogicException('Unexpected error source value.');
    }

    /**
     * @param array $source
     * @return ErrorSource
     */
    public static function fromArray(array $source): self
    {
        return new self(
            $source['pointer'] ?? null,
            $source['parameter'] ?? null
        );
    }

    /**
     * ErrorSource constructor.
     *
     * @param string|null $pointer
     * @param string|null $parameter
     */
    public function __construct(string $pointer = null, string $parameter = null)
    {
        $this->pointer = $pointer;
        $this->parameter = $parameter;
    }

    /**
     * The JSON Pointer [RFC6901] to the associated entity in the request document.
     *
     * E.g. "/data" for a primary data object, or "/data/attributes/title" for a specific attribute.
     *
     * @return string|null
     */
    public function pointer(): ?string
    {
        return $this->pointer;
    }

    /**
     * Add a JSON Pointer.
     *
     * @param string|null $pointer
     * @return $this
     */
    public function withPointer(?string $pointer): self
    {
        $this->pointer = $pointer;

        return $this;
    }

    /**
     * Remove the JSON pointer.
     *
     * @return $this
     */
    public function withoutPointer(): self
    {
        $this->pointer = null;

        return $this;
    }


    /**
     * A string indicating which URI query parameter caused the error.
     *
     * @return string|null
     */
    public function parameter(): ?string
    {
        return $this->parameter;
    }

    /**
     * Add a string indicating which URI query parameter caused the error.
     *
     * @param string|null $parameter
     * @return $this
     */
    public function withParameter(?string $parameter): self
    {
        $this->parameter = $parameter;

        return $this;
    }

    /**
     * Remove the source parameter.
     *
     * @return $this
     */
    public function withoutParameter(): self
    {
        $this->parameter = null;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->pointer) && empty($this->parameter);
    }

    /**
     * @return bool
     */
    public function isNotEmpty(): bool
    {
        return !$this->isEmpty();
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return array_filter([
            'parameter' => $this->parameter,
            'pointer' => $this->pointer,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return $this->toArray() ?: null;
    }

}
