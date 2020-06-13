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
use function collect;
use function count;
use function is_array;

class FieldSets implements Arrayable, IteratorAggregate, Countable
{

    /**
     * @var array
     */
    private $stack;

    /**
     * @param FieldSets|array $value
     * @return FieldSets
     */
    public static function cast($value): self
    {
        if ($value instanceof self) {
            return $value;
        }

        if (is_array($value)) {
            return self::fromArray($value);
        }

        throw new UnexpectedValueException('Unexpected field sets value.');
    }

    /**
     * @param array $value
     * @return FieldSets
     */
    public static function fromArray(array $value): self
    {
        return new self(...collect($value)->map(function (array $fields, string $type) {
            return new FieldSet($type, ...$fields);
        })->values());
    }

    /**
     * FieldSets constructor.
     *
     * @param FieldSet ...$fieldSets
     */
    public function __construct(FieldSet ...$fieldSets)
    {
        $this->stack = [];
        $this->push(...$fieldSets);
    }

    /**
     * @param FieldSet ...$fieldSets
     * @return $this
     */
    public function push(FieldSet ...$fieldSets)
    {
        foreach ($fieldSets as $fieldSet) {
            $this->stack[$fieldSet->type()] = $fieldSet;
        }

        return $this;
    }

    /**
     * @param string ...$keys
     * @return $this
     */
    public function forget(string ...$keys): self
    {
        foreach ($keys as $key) {
            unset($this->stack[$key]);
        }

        return $this;
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
    public function toArray()
    {
        return collect($this->stack)->map(function (FieldSet $fieldSet) {
            return $fieldSet->fields();
        })->all();
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

}
