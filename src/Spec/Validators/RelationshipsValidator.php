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
use LaravelJsonApi\Spec\Document;
use LaravelJsonApi\Spec\Factory;
use LaravelJsonApi\Spec\Specification;
use LaravelJsonApi\Spec\Translator;

class RelationshipsValidator
{

    /**
     * @var Specification
     */
    private Specification $spec;

    /**
     * @var Translator
     */
    private Translator $translator;

    /**
     * @var Factory
     */
    private Factory $factory;

    /**
     * RelationshipsValidator constructor.
     *
     * @param Specification $spec
     * @param Translator $translator
     * @param Factory $factory
     */
    public function __construct(Specification $spec, Translator $translator, Factory $factory)
    {
        $this->spec = $spec;
        $this->translator = $translator;
        $this->factory = $factory;
    }

    /**
     * Validate the `/data/relationships` member.
     *
     * @param Document $document
     * @param \Closure $next
     * @return Document
     */
    public function validate(Document $document, \Closure $next): Document
    {
        $data = $document->data ?? null;

        if ($data && property_exists($data, 'relationships')) {
            $document->errors()->merge(
                $this->accept($document->type(), $data->relationships)
            );
        }

        return $next($document);
    }

    /**
     * @param string $resourceType
     * @param $relationships
     * @return ErrorList
     */
    private function accept(string $resourceType, $relationships): ErrorList
    {
        $errors = new ErrorList();

        if (!is_object($relationships)) {
            return $errors->push(
                $this->translator->memberNotObject('/data', 'relationships')
            );
        }

        $fields = collect($this->spec->fields($resourceType))
            ->whereInstanceOf(Relation::class)
            ->keyBy(fn($relation) => $relation->name());

        foreach ($relationships as $name => $value) {
            if ('type' === $name || 'id' === $name) {
                $errors->push($this->translator->memberFieldNotAllowed('/data', 'relationships', $name));
                continue;
            }

            if ($field = $fields->get($name)) {
                $errors->merge($this->acceptRelation($field, $value));
                continue;
            }

            $errors->push($this->translator->memberFieldNotSupported('/data', 'relationships', $name));
        }

        return $errors;
    }

    /**
     * @param Relation $relation
     * @param $value
     * @return ErrorList
     */
    private function acceptRelation(Relation $relation, $value): ErrorList
    {
        if ($relation->toMany()) {
            return $this->factory->createToManyValue(
                "/data/relationships/{$relation->name()}",
                $value
            )->allErrors();
        }

        return $this->factory->createToOneValue(
            "/data/relationships/{$relation->name()}",
            $value
        )->allErrors();
    }
}
