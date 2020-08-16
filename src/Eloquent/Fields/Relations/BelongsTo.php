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

namespace LaravelJsonApi\Eloquent\Fields\Relations;

use LaravelJsonApi\Core\Support\Str;

class BelongsTo extends Relation
{

    /**
     * Create a to-one relation.
     *
     * @param string $fieldName
     * @param string|null $relation
     * @return BelongsTo
     */
    public static function make(string $fieldName, string $relation = null): BelongsTo
    {
        return new self($fieldName, $relation);
    }

    /**
     * @inheritDoc
     */
    public function toOne(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    protected function guessInverse(): string
    {
        return Str::dasherize(
            Str::plural($this->name())
        );
    }

}
