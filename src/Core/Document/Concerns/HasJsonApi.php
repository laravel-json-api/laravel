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

use LaravelJsonApi\Core\Document\Document;
use LaravelJsonApi\Core\Document\JsonApi;

trait HasJsonApi
{

    /**
     * @var JsonApi|null
     */
    private $jsonApi;

    /**
     * @return JsonApi
     */
    public function jsonApi(): JsonApi
    {
        if ($this->jsonApi) {
            return $this->jsonApi;
        }

        return $this->jsonApi = new JsonApi();
    }

    /**
     * @param mixed $jsonApi
     * @return $this
     */
    public function withJsonApi($jsonApi): self
    {
        $this->jsonApi = JsonApi::cast($jsonApi);

        return $this;
    }

    /**
     * @return $this
     */
    public function withoutJsonApi(): self
    {
        $this->jsonApi = null;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasJsonApi(): bool
    {
        if ($this->jsonApi) {
            return $this->jsonApi->isNotEmpty();
        }

        return false;
    }

    /**
     * @return bool
     */
    public function doesntHaveJsonApi(): bool
    {
        return !$this->hasJsonApi();
    }
}
