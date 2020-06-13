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
use Illuminate\Contracts\Support\Arrayable;
use IteratorAggregate;
use JsonSerializable;
use function collect;

class Hash implements ArrayAccess, Arrayable, Countable, IteratorAggregate, JsonSerializable
{

    use Concerns\Hashable;

    /**
     * @param $value
     * @return static
     */
    public static function cast($value): self
    {
        if ($value instanceof self) {
            return $value;
        }

        if (is_array($value)) {
            return new self($value);
        }

        throw new \LogicException('Unexpected JSON hash value.');
    }

    /**
     * Hash constructor.
     *
     * @param array $value
     */
    public function __construct(array $value = [])
    {
        $this->value = $value;
        $this->sorted();
    }

    /**
     * Set the value of the given key.
     *
     * @param string $key
     * @param $value
     * @return $this
     */
    public function put(string $key, $value): self
    {
        $this->value[$key] = $value;

        return $this;
    }

    /**
     * Remove keys from the hash.
     *
     * @param string ...$keys
     * @return $this
     */
    public function forget(string ...$keys): self
    {
        foreach ($keys as $key) {
            unset($this->value[$key]);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return collect($this->value)->toArray();
    }

}
