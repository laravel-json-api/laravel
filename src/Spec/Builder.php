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
use LogicException;
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
    private ?string $expectedType = null;

    /**
     * @var string|null
     */
    private ?string $expectedId = null;

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
     * Expect the supplied resource type and id.
     *
     * @param string $resourceType
     * @param string|null $resourceId
     * @return $this
     */
    public function expects(string $resourceType, ?string $resourceId): self
    {
        $this->expectedType = $resourceType;
        $this->expectedId = $resourceId;

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

        return $this->pipeline
            ->send(new Document($json, $this->expectedType, $this->expectedId))
            ->through($this->pipes())
            ->via('validate')
            ->thenReturn();
    }

    /**
     * @return string[]
     */
    private function pipes(): array
    {
        if ($this->expectedType) {
            return [
                Validators\DataValidator::class,
                Validators\TypeValidator::class,
                Validators\ClientIdValidator::class,
                Validators\IdValidator::class,
                Validators\FieldsValidator::class,
                Validators\AttributesValidator::class,
                Validators\RelationshipsValidator::class,
            ];
        }

        throw new LogicException('Cannot determine validation pipes.');
    }
}
