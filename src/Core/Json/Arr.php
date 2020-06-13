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

namespace LaravelJsonApi\Core\Json;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use function array_values;

class Arr implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable
{

    use Concerns\ArrayList;

    /**
     * Arr constructor.
     *
     * @param array $value
     */
    public function __construct(array $value)
    {
        $this->value = $value;
    }

    /**
     * Add values to the start of the array.
     *
     * @param mixed ...$values
     * @return $this
     */
    public function prepend(...$values): self
    {
        $this->value = $values + array_values($this->value);

        return $this;
    }

    /**
     * Push values onto the end of the array.
     *
     * @param mixed ...$values
     * @return $this
     */
    public function push(...$values): self
    {
        $this->value = array_values($this->value) + $values;

        return $this;
    }
}
