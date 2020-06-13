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

namespace LaravelJsonApi\Core\Query;

use Illuminate\Support\Str;
use InvalidArgumentException;
use UnexpectedValueException;
use function is_string;

class SortField
{

    /**
     * @var string
     */
    private $name;

    /**
     * @var bool
     */
    private $ascending;

    /**
     * @param SortField|string $value
     * @return static
     */
    public static function cast($value): self
    {
        if ($value instanceof self) {
            return $value;
        }

        if (is_string($value) && !empty($value)) {
            return self::fromString($value);
        }

        throw new UnexpectedValueException('Unexpected sort field value.');
    }

    /**
     * @param string $value
     * @return SortField
     */
    public static function fromString(string $value): self
    {
        if (Str::startsWith($value, '-')) {
            return new self(ltrim($value, '-'), false);
        }

        return new self($value);
    }

    /**
     * SortField constructor.
     *
     * @param string $name
     * @param bool $ascending
     */
    public function __construct(string $name, bool $ascending = true)
    {
        if (empty($name)) {
            throw new InvalidArgumentException('Expecting a non-empty string.');
        }

        $this->name = $name;
        $this->ascending = $ascending;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        if ($this->isAscending()) {
            return $this->name;
        }

        return "-{$this->name}";
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function isAscending(): bool
    {
        return !$this->isDescending();
    }

    /**
     * @return bool
     */
    public function isDescending(): bool
    {
        return false === $this->ascending;
    }
}
