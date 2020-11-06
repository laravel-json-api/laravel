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

use LaravelJsonApi\Core\Document\Error;
use LaravelJsonApi\Spec\Document;
use LaravelJsonApi\Spec\Translator;

class AttributesValidator
{

    /**
     * @var Translator
     */
    private Translator $translator;

    /**
     * AttributesValidator constructor.
     *
     * @param Translator $translator
     */
    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Validate the `/data/attributes` member.
     *
     * @param Document $document
     * @param \Closure $next
     * @return Document
     */
    public function validate(Document $document, \Closure $next): Document
    {
        $data = $document->data;

        if (property_exists($data, 'attributes') && $errors = $this->accept($data->attributes)) {
            $document->errors()->push(...$errors);
        }

        return $next($document);
    }

    /**
     * @param $value
     * @return array|null
     */
    private function accept($value): ?array
    {
        if (!is_object($value)) {
            return [$this->translator->memberNotObject('/data', 'attributes')];
        }

        return collect(['type', 'id'])
            ->filter(fn($field) => property_exists($value, $field))
            ->map(fn($field) => $this->translator->memberFieldNotAllowed('/data', 'attributes', $field))
            ->all();
    }
}
