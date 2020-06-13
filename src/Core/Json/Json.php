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

use JsonSerializable;
use LogicException;
use function is_array;

final class Json
{

    /**
     * Create a JSON array list (array with zero-indexed numeric keys).
     *
     * @param $value
     * @return Arr
     */
    public static function arr($value): Arr
    {
        if ($value instanceof Arr) {
            return $value;
        }

        if ($value instanceof JsonSerializable) {
            $value = $value->jsonSerialize();
        }

        if (is_array($value)) {
            return new Arr($value);
        }

        throw new LogicException('Unexpected JSON array list value.');
    }

    /**
     * Create a JSON hash (array with string keys).
     *
     * @param mixed $value
     * @return Hash
     */
    public static function hash($value): Hash
    {
        if ($value instanceof Hash) {
            return $value;
        }

        if ($value instanceof JsonSerializable) {
            $value = $value->jsonSerialize();
        }

        if (is_array($value)) {
            return new Hash($value);
        }

        throw new LogicException('Unexpected JSON array hash value.');
    }
}
