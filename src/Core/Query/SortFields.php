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

use Countable;
use Illuminate\Contracts\Support\Arrayable;
use IteratorAggregate;
use UnexpectedValueException;
use function array_map;
use function collect;
use function count;
use function explode;
use function implode;
use function is_array;
use function is_string;

class SortFields implements IteratorAggregate, Countable, Arrayable
{

    /**
     * @var SortField[]
     */
    private array $stack;

    /**
     * @param SortFields|SortField|array|string $value
     * @return SortFields
     */
    public static function cast($value): self
    {
        if ($value instanceof self) {
            return $value;
        }

        if ($value instanceof SortField) {
            return new self($value);
        }

        if (is_array($value)) {
            return self::fromArray($value);
        }

        if (is_string($value)) {
            return self::fromString($value);
        }

        throw new UnexpectedValueException('Unexpected sort fields value.');
    }

    /**
     * @param array $values
     * @return SortFields
     */
    public static function fromArray(array $values): self
    {
        return new self(...collect($values)->map(function ($field) {
            return SortField::cast($field);
        }));
    }

    /**
     * @param string $value
     * @return SortFields
     */
    public static function fromString(string $value): self
    {
        return new self(...collect(explode(',', $value))->map(function ($field) {
            return SortField::fromString($field);
        }));
    }

    /**
     * SortFields constructor.
     *
     * @param SortField ...$fields
     */
    public function __construct(SortField ...$fields)
    {
        $this->stack = $fields;
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
        return implode(',', $this->stack);
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->stack);
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
    public function getIterator()
    {
        yield from $this->stack;
    }

    /**
     * @inheritDoc
     */
    public function count()
    {
        return count($this->stack);
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return array_map(function (SortField $field) {
            return $field->toString();
        }, $this->stack);
    }

}
