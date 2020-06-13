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

namespace LaravelJsonApi\Core\Resources;

use Closure;
use JsonSerializable;
use LaravelJsonApi\Core\Contracts\Resources\Skippable;
use LogicException;

class ConditionalAttr implements JsonSerializable, Skippable
{

    /**
     * @var bool
     */
    private $check;

    /**
     * @var mixed
     */
    private $value;

    /**
     * ConditionalAttr constructor.
     *
     * @param bool $bool
     * @param mixed $value
     */
    public function __construct(bool $bool, $value)
    {
        $this->check = $bool;
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function __invoke()
    {
        if ($this->value instanceof Closure) {
            return ($this->value)();
        }

        return $this->value;
    }

    /**
     * Should the attribute be skipped when encoding?
     *
     * @return bool
     */
    public function skip(): bool
    {
        return false === $this->check;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        if (false === $this->skip()) {
            return ($this)();
        }

        throw new LogicException('Conditional attribute must not be serialized.');
    }

}
