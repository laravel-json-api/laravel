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

namespace LaravelJsonApi\Spec;

use Illuminate\Pipeline\Pipeline;
use function json_decode;

class Builder
{

    /**
     * @var Pipeline
     */
    private Pipeline $pipeline;

    /**
     * @var string|null
     */
    private ?string $expects = null;

    /**
     * Builder constructor.
     *
     * @param Pipeline $pipeline
     */
    public function __construct(Pipeline $pipeline)
    {
        $this->pipeline = $pipeline;
    }

    /**
     * Expect the supplied resource type.
     *
     * @param string $resourceType
     * @return $this
     */
    public function expects(string $resourceType): self
    {
        $this->expects = $resourceType;

        return $this;
    }

    /**
     * @param string|object $json
     * @return Document
     */
    public function build($json): Document
    {
        if (is_string($json)) {
            $json = json_decode($json, false, 512, JSON_THROW_ON_ERROR);
        }

        if (!is_object($json)) {
            throw new \InvalidArgumentException('Expecting a string or object.');
        }

        $pipes = [
            Validators\DataValidator::class,
            Validators\TypeValidator::class,
            Validators\ClientIdValidator::class,
            Validators\FieldsValidator::class,
            Validators\AttributesValidator::class,
            Validators\RelationshipsValidator::class,
            Validators\RelationshipValidator::class,
        ];

        return $this->pipeline
            ->send(new Document($json, $this->expects))
            ->through($pipes)
            ->via('validate')
            ->thenReturn();
    }
}
