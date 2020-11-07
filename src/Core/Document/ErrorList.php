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

use Countable;
use IteratorAggregate;
use LaravelJsonApi\Contracts\Serializable;
use LogicException;
use function array_merge;
use function collect;

class ErrorList implements Serializable, Countable, IteratorAggregate
{

    use Concerns\Serializable;

    /**
     * @var array
     */
    private array $stack;

    /**
     * Create a list of JSON API error objects.
     *
     * @param ErrorList|Error|array $value
     * @return ErrorList
     */
    public static function cast($value): ErrorList
    {
        if ($value instanceof ErrorList) {
            return $value;
        }

        if ($value instanceof Error) {
            return new ErrorList($value);
        }

        if (is_array($value)) {
            return ErrorList::fromArray($value);
        }

        throw new LogicException('Unexpected error collection value.');
    }

    /**
     * @param array $array
     * @return ErrorList
     */
    public static function fromArray(array $array): self
    {
        $errors = new self();
        $errors->stack = collect($array)->map(function ($error) {
            return Error::cast($error);
        })->values()->all();

        return $errors;
    }

    /**
     * Errors constructor.
     *
     * @param Error ...$errors
     */
    public function __construct(Error ...$errors)
    {
        $this->stack = $errors;
    }

    /**
     * Add errors.
     *
     * @param Error ...$errors
     * @return $this
     */
    public function push(Error ...$errors): self
    {
        foreach ($errors as $error) {
            $this->stack[] = $error;
        }

        return $this;
    }

    /**
     * Merge errors.
     *
     * @param iterable $errors
     * @return $this
     */
    public function merge(iterable $errors): self
    {
        if ($errors instanceof static) {
            $this->stack = array_merge($this->stack, $errors->stack);
            return $this;
        }

        return $this->push(...$errors);
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
        return collect($this->stack)->toArray();
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return $this->stack;
    }

}
