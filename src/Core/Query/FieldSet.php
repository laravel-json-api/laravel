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

namespace LaravelJsonApi\Core\Query;

use Countable;
use InvalidArgumentException;
use IteratorAggregate;
use function count;

class FieldSet implements IteratorAggregate, Countable
{

    /**
     * @var string
     */
    private string $resourceType;

    /**
     * @var string[]
     */
    private array $fields;

    /**
     * FieldSet constructor.
     *
     * @param string $resourceType
     * @param string ...$fields
     */
    public function __construct(string $resourceType, string ...$fields)
    {
        if (empty($resourceType)) {
            throw new InvalidArgumentException('Expecting a non-empty resoruce type.');
        }

        $this->resourceType = $resourceType;
        $this->fields = $fields;
    }

    /**
     * The resource type the field set belongs to.
     *
     * @return string
     */
    public function type(): string
    {
        return $this->resourceType;
    }

    /**
     * The fields to serialize in the output.
     *
     * @return array
     */
    public function fields(): array
    {
        return $this->fields;
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->fields);
    }

    /**
     * @return bool
     */
    public function isNotEmpty(): bool
    {
        return !$this->isEmpty();
    }

    /**
     * @inheritDoc
     */
    public function getIterator()
    {
        yield from $this->fields;
    }

    /**
     * @inheritDoc
     */
    public function count()
    {
        return count($this->fields);
    }

}
