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

namespace LaravelJsonApi\Core\Contracts\Resources;

use Generator;
use LaravelJsonApi\Core\Contracts\Document\ResourceObject;

interface Container
{

    /**
     * Resolve the value to a resource object or a cursor of resource objects.
     *
     * @param mixed $value
     *      a resource object, record or an iterable of records.
     * @return ResourceObject|Generator
     */
    public function resolve($value);

    /**
     * Can the provided record be converted to a resource object?
     *
     * @param mixed $record
     * @return bool
     */
    public function exists($record): bool;

    /**
     * Create a resource object for the supplied record.
     *
     * @param mixed $record
     * @return ResourceObject
     */
    public function create($record): ResourceObject;

    /**
     * Create resource objects for the supplied records.
     *
     * @param iterable $records
     * @return Generator
     */
    public function cursor(iterable $records): Generator;
}
