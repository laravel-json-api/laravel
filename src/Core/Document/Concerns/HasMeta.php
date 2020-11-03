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

use LaravelJsonApi\Core\Json\Hash;
use LaravelJsonApi\Core\Json\Json;

trait HasMeta
{

    /**
     * @var Hash|null
     */
    private ?Hash $meta = null;

    /**
     * Get the meta member.
     *
     * @return Hash
     */
    public function meta(): Hash
    {
        if ($this->meta) {
            return $this->meta;
        }

        return $this->meta = new Hash();
    }

    /**
     * Replace the meta member.
     *
     * @param mixed|null $meta
     * @return $this
     */
    public function withMeta($meta): self
    {
        $this->meta = Json::hash($meta);

        return $this;
    }

    /**
     * Remove meta.
     *
     * @return $this
     */
    public function withoutMeta(): self
    {
        $this->meta = null;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasMeta(): bool
    {
        if ($this->meta) {
            return $this->meta->isNotEmpty();
        }

        return false;
    }

    /**
     * @return bool
     */
    public function doesntHaveMeta(): bool
    {
        return !$this->hasMeta();
    }
}
