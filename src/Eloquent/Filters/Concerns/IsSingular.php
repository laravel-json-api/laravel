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

namespace LaravelJsonApi\Eloquent\Filters\Concerns;

trait IsSingular
{

    /**
     * @var bool
     */
    private bool $singular = false;

    /**
     * @return $this
     */
    public function singular(): self
    {
        $this->singular = true;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSingular(): bool
    {
        return $this->singular;
    }
}
