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
use LaravelJsonApi\Core\Query\RelationshipPath;
use LaravelJsonApi\Eloquent\Fields\Relations\Relation;
use LogicException;
use function implode;
use function iterator_to_array;

class EagerLoadPath implements IteratorAggregate
{

    /**
     * @var Schema
     */
    private Schema $schema;

    /**
     * @var RelationshipPath
     */
    private RelationshipPath $path;

    /**
     * EagerLoadPath constructor.
     *
     * @param Schema $schema
     * @param RelationshipPath $path
     */
    public function __construct(Schema $schema, RelationshipPath $path)
    {
        $this->schema = $schema;
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return implode('.', $this->all());
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
        $schema = $this->schema;

        foreach ($this->path->names() as $field) {
            $relation = $schema->relationship($field);

            if ($relation instanceof Relation && $relation->isIncludePath()) {
                $schema = $relation->schema();
                yield $relation->relation();
                continue;
            }

            throw new LogicException("Field {$field} is not a valid Eloquent include path.");
        }
    }

}
