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

namespace LaravelJsonApi\Core\Rules;

use Illuminate\Support\Collection;
use LaravelJsonApi\Core\Contracts\Schema\Schema;

class AllowedSortParameters extends AbstractAllowedRule
{

    /**
     * Create an allowed sort parameter rule for the supplied schema.
     *
     * @param Schema $schema
     * @return static
     */
    public static function make(Schema $schema): self
    {
        return new self($schema->sortable());
    }

    /**
     * @inheritDoc
     */
    protected function extract($value): Collection
    {
        $params = is_string($value) ? explode(',', $value) : [];

        return collect($params)->map(function ($param) {
            return ltrim($param, '+-');
        })->unique()->values();
    }

}
