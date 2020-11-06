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
use LaravelJsonApi\Core\Contracts\Schema\Container as SchemaContainer;
use LaravelJsonApi\Core\Contracts\Schema\Schema;
use LaravelJsonApi\Core\Schema\IncludePathIterator;
use function collect;

class AllowedIncludePaths extends AbstractAllowedRule
{

    /**
     * Create an allowed include path rule for the supplied schema and depth.
     *
     * @param SchemaContainer $schemas
     * @param Schema $schema
     * @param int $depth
     * @return AllowedIncludePaths
     */
    public static function make(SchemaContainer $schemas, Schema $schema, int $depth): self
    {
        return new self(new IncludePathIterator(
            $schemas,
            $schema,
            $depth
        ));
    }

    /**
     * @inheritDoc
     */
    protected function extract($value): Collection
    {
        $paths = is_string($value) ? explode(',', $value) : [];

        return collect($paths);
    }

}
