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

namespace LaravelJsonApi\Eloquent\Filters;

use LaravelJsonApi\Core\Support\Str;
use LaravelJsonApi\Eloquent\Contracts\Filter;

class Scope implements Filter
{

    use Concerns\DeserializesValue;

    /**
     * @var string
     */
    private string $name;

    /**
     * @var string
     */
    private string $scope;

    /**
     * Scope constructor.
     *
     * @param string $name
     * @param string|null $scope
     */
    public function __construct(string $name, string $scope = null)
    {
        $this->name = $name;
        $this->scope = $scope ?: $this->guessScope();
    }

    /**
     * @inheritDoc
     */
    public function key(): string
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function apply($query, $value)
    {
        return $query->{$this->scope}(
            $this->deserialize($value)
        );
    }

    /**
     * @return string
     */
    private function guessScope(): string
    {
        return Str::camel($this->name);
    }
}
