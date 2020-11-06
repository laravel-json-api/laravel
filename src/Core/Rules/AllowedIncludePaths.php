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
use LaravelJsonApi\Contracts\Schema\Schema;
use function collect;

class AllowedIncludePaths extends AbstractAllowedRule
{

    /**
     * Create an allowed include path rule for the supplied schema.
     *
     * @param Schema $schema
     * @return AllowedIncludePaths
     */
    public static function make(Schema $schema): self
    {
        return new self($schema->includePaths());
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
