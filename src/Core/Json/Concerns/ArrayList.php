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

namespace LaravelJsonApi\Core\Json\Concerns;

use Generator;
use LaravelJsonApi\Core\Support\Arr;
use LogicException;
use function iterator_to_array;

trait ArrayList
{

    /**
     * @var array
     */
    private $value;

    /**
     * @inheritDoc
     */
    public function offsetExists($offset)
    {
        return Arr::exists($this->value, $offset);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset)
    {
        return $this->value[$offset];
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value)
    {
        if ($this->next() !== intval($offset)) {
            throw new LogicException('Can only set the next element in the array list.');
        }

        $this->value[$offset] = $value;
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset)
    {
        Arr::forget($this->value, $offset);
    }

    /**
     * @inheritDoc
     */
    public function count()
    {
        return count($this->value);
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return $this->value;
    }

    /**
     * @inheritDoc
     */
    public function cursor(): Generator
    {
        foreach ($this->value as $value) {
            yield $value;
        }
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return iterator_to_array($this->cursor());
    }

    /**
     * @inheritDoc
     */
    public function getIterator()
    {
        return $this->cursor();
    }

    /**
     * Get the next index.
     *
     * @return int
     */
    public function next(): int
    {
        return $this->count() + 1;
    }
}
