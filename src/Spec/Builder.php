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

class Builder
{

    /**
     * @var Translator
     */
    private Translator $translator;

    /**
     * @var Pipeline
     */
    private Pipeline $pipeline;

    /**
     * @var string|null
     */
    private ?string $expects = null;

    /**
     * @var bool
     */
    private bool $clientIds = false;

    /**
     * Builder constructor.
     *
     * @param Translator $translator
     * @param Pipeline $pipeline
     */
    public function __construct(Translator $translator, Pipeline $pipeline)
    {
        $this->translator = $translator;
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
     * Set whether client ids are allowed.
     *
     * @param bool $supported
     * @return $this
     */
    public function clientIds(bool $supported = true): self
    {
        $this->clientIds = $supported;

        return $this;
    }

    /**
     * @param $json
     * @return Document
     */
    public function build($json): Document
    {
        $pipes = [
            new Validators\DataValidator($this->translator),
            new Validators\TypeValidator($this->translator, $this->expects),
            new Validators\ClientIdValidator($this->translator, $this->expects, $this->clientIds),
            new Validators\AttributesValidator($this->translator),
            new Validators\RelationshipsValidator($this->translator),
            new Validators\RelationshipValidator($this->translator),
            new Validators\FieldsValidator($this->translator),
        ];

        return $this->pipeline
            ->send(Document::cast($json))
            ->through($pipes)
            ->via('validate')
            ->thenReturn();
    }
}
