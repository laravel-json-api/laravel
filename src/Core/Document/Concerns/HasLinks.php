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

namespace LaravelJsonApi\Core\Document\Concerns;

use LaravelJsonApi\Core\Document\Links;

trait HasLinks
{

    /**
     * @var Links|null
     */
    private ?Links $links = null;

    /**
     * Get the links member.
     *
     * @return Links
     */
    public function links(): Links
    {
        if ($this->links) {
            return $this->links;
        }

        return $this->links = new Links();
    }

    /**
     * Replace the links member.
     *
     * @param mixed|null $links
     * @return $this
     */
    public function setLinks($links): self
    {
        $this->links = Links::cast($links);

        return $this;
    }

    /**
     * Remove links.
     *
     * @return $this
     */
    public function withoutLinks(): self
    {
        $this->links = null;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasLinks(): bool
    {
        if ($this->links) {
            return $this->links->isNotEmpty();
        }

        return false;
    }

    /**
     * @return bool
     */
    public function doesntHaveLinks(): bool
    {
        return !$this->hasLinks();
    }
}
