<?php
/*
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

trait MatchesIds
{

    /**
     * @var string
     */
    private string $pattern = '[0-9]+';

    /**
     * @var string
     */
    private string $flags = 'iD';

    /**
     * Get the regex pattern for the ID field.
     *
     * @return string
     */
    public function pattern(): string
    {
        return $this->pattern;
    }

    /**
     * Mark the ID field as a UUID.
     *
     * @return $this
     */
    public function uuid(): self
    {
        $this->pattern = '[\da-f]{8}-[\da-f]{4}-[\da-f]{4}-[\da-f]{4}-[\da-f]{12}';

        return $this;
    }

    /**
     * Mark the ID as case-sensitive.
     *
     * @return $this
     */
    public function matchCase(): self
    {
        $this->flags = 'D';

        return $this;
    }

    /**
     * Does the value match the ID's pattern?
     *
     * @param string $resourceId
     * @return bool
     */
    public function match(string $resourceId): bool
    {
        return 1 === preg_match("/^{$this->pattern}$/{$this->flags}", $resourceId);
    }
}
