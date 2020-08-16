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

namespace LaravelJsonApi\Eloquent;

use IteratorAggregate;
use LaravelJsonApi\Core\Query\IncludePaths;
use function iterator_to_array;

class EagerLoader implements IteratorAggregate
{

    /**
     * @var Schema
     */
    private Schema $schema;

    /**
     * @var IncludePaths
     */
    private IncludePaths $paths;

    /**
     * EagerLoader constructor.
     *
     * @param Schema $schema
     * @param IncludePaths $paths
     * @TODO need to support sparse fields sets when eager loading.
     */
    public function __construct(Schema $schema, IncludePaths $paths)
    {
        $this->schema = $schema;
        $this->paths = $paths;
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return iterator_to_array($this);
    }

    /**
     * @inheritDoc
     */
    public function getIterator()
    {
        foreach ($this->paths as $path) {
            yield (string) new EagerLoadPath($this->schema, $path);
        }
    }

}
