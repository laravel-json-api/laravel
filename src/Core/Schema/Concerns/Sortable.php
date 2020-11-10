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

namespace LaravelJsonApi\Core\Schema\Concerns;

trait Sortable
{

    /**
     * @var bool
     */
    private bool $sortable = false;

    /**
     * Mark the field as sortable.
     *
     * @return $this
     */
    public function sortable(): self
    {
        $this->sortable = true;

        return $this;
    }

    /**
     * Mark the field as not sortable.
     *
     * @return $this
     */
    public function notSortable(): self
    {
        $this->sortable = false;

        return $this;
    }

    /**
     * Is the field sortable?
     *
     * @return bool
     */
    public function isSortable(): bool
    {
        return $this->sortable;
    }
}
