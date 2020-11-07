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

namespace LaravelJsonApi\Spec\Validators;

use LaravelJsonApi\Contracts\Schema\Relation;
use LaravelJsonApi\Core\Document\ErrorList;
use LaravelJsonApi\Spec\Factory;
use LaravelJsonApi\Spec\RelationDocument;
use LaravelJsonApi\Spec\Specification;
use LogicException;
use function sprintf;

class RelationValidator
{

    /**
     * @var Specification
     */
    private Specification $spec;

    /**
     * @var Factory
     */
    private Factory $factory;

    /**
     * RelationValidator constructor.
     *
     * @param Specification $spec
     * @param Factory $factory
     */
    public function __construct(Specification $spec, Factory $factory)
    {
        $this->spec = $spec;
        $this->factory = $factory;
    }

    /**
     * Validate the relationship at the `/data` path.
     *
     * @param RelationDocument $document
     * @param \Closure $next
     * @return RelationDocument
     */
    public function validate(RelationDocument $document, \Closure $next): RelationDocument
    {
        if (!$name = $document->relation()) {
            throw new LogicException('Expecting to be validating a relationship document.');
        }

        /** @var Relation|null $field */
        $field = collect($this->spec->fields($document->type()))
            ->whereInstanceOf(Relation::class)
            ->first(fn(Relation $relation) => $name === $relation->name());

        if ($field) {
            $document->errors()->merge(
                $this->accept($field, $document->toBase())
            );
            return $next($document);
        }

        throw new LogicException(sprintf(
            'Expecting relation %s to exist for resource type %s.',
            $name,
            $document->type()
        ));
    }

    /**
     * @param Relation $relation
     * @param $value
     * @return ErrorList
     */
    private function accept(Relation $relation, $value): ErrorList
    {
        if ($relation->toMany()) {
            return $this->factory->createToManyValue(
                '/',
                $value
            )->allErrors();
        }

        return $this->factory->createToOneValue(
            '/',
            $value
        )->allErrors();
    }
}
