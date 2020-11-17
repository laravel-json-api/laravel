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
use Illuminate\Support\Enumerable;
use IteratorAggregate;
use UnexpectedValueException;
use function collect;
use function count;
use function explode;
use function is_array;
use function is_string;

class IncludePaths implements IteratorAggregate, Countable, Arrayable
{

    /**
     * @var RelationshipPath[]
     */
    private array $stack;

    /**
     * @param IncludePaths|RelationshipPath|Enumerable|array|string|null $value
     * @return IncludePaths
     */
    public static function cast($value): self
    {
        if ($value instanceof self) {
            return $value;
        }

        if ($value instanceof RelationshipPath) {
            return new self($value);
        }

        if (is_array($value) || $value instanceof Enumerable) {
            return self::fromArray($value);
        }

        if (is_string($value)) {
            return self::fromString($value);
        }

        if (is_null($value)) {
            return new self();
        }

        throw new UnexpectedValueException('Unexpected include paths value.');
    }

    /**
     * @param array|Enumerable $paths
     * @return IncludePaths
     */
    public static function fromArray($paths): self
    {
        if (!is_array($paths) && !$paths instanceof Enumerable) {
            throw new \InvalidArgumentException('Expecting an array or enumerable object.');
        }

        return new self(...collect($paths)->map(function ($path) {
            return RelationshipPath::cast($path);
        }));
    }

    /**
     * @param string $paths
     * @return IncludePaths
     */
    public static function fromString(string $paths): self
    {
        return new self(...collect(explode(',', $paths))->map(function (string $path) {
            return RelationshipPath::fromString($path);
        }));
    }

    /**
     * @param IncludePaths|RelationshipPath|array|string|null $value
     * @return IncludePaths|null
     */
    public static function nullable($value): ?self
    {
        if (is_null($value)) {
            return null;
        }

        return self::cast($value);
    }

    /**
     * IncludePaths constructor.
     *
     * @param RelationshipPath ...$paths
     */
    public function __construct(RelationshipPath ...$paths)
    {
        $this->stack = $paths;
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
     * @return RelationshipPath[]
     */
    public function all(): array
    {
        return $this->stack;
    }

    /**
     * @param int $num
     * @return $this
     */
    public function skip(int $num): self
    {
        $items = collect($this->stack)
            ->map(fn(RelationshipPath $path) => $path->skip($num))
            ->filter()
            ->values();

        return new self(...$items);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return collect($this->stack)->map(function (RelationshipPath $path) {
            return $path->toString();
        })->all();
    }

}
