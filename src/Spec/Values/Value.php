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

namespace LaravelJsonApi\Spec\Values;

use Illuminate\Support\Str;
use LaravelJsonApi\Core\Document\ErrorList;

abstract class Value
{

    /**
     * @var string
     */
    protected string $path;

    /**
     * @var ErrorList|null
     */
    private ?ErrorList $errors = null;

    /**
     * Validate the object.
     *
     * @return ErrorList
     */
    abstract protected function validate(): ErrorList;

    /**
     * @return ErrorList
     */
    public function errors(): ErrorList
    {
        if ($this->errors) {
            return $this->errors;
        }

        return $this->errors = $this->validate();
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return $this->errors()->isEmpty();
    }

    /**
     * @return bool
     */
    public function invalid(): bool
    {
        return !$this->valid();
    }

    /**
     * Get the path to the object that holds the value.
     *
     * @return string
     */
    protected function parent(): string
    {
        return Str::beforeLast($this->path, '/') ?: '/';
    }

    /**
     * Get the member name that holds the value.
     *
     * @return string
     */
    protected function member(): string
    {
        return Str::afterLast($this->path, '/');
    }
}
