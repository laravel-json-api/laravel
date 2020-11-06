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

class TypeValidator
{

    /**
     * @var Translator
     */
    private Translator $translator;

    /**
     * @var string
     */
    private string $expects;

    /**
     * TypeValidator constructor.
     *
     * @param Translator $translator
     * @param string $expects
     */
    public function __construct(Translator $translator, string $expects)
    {
        $this->translator = $translator;
        $this->expects = $expects;
    }

    /**
     * Validate the `/data/type` member of the document.
     *
     * @param Document $document
     * @param \Closure $next
     * @return Document
     */
    public function validate(Document $document, \Closure $next): Document
    {
        $data = $document->data;

        if (!property_exists($data, 'type')) {
            $document->errors()->push(
                $this->translator->memberRequired('/data', 'type')
            );
            return $next($document);
        }

        if ($error = $this->accept($data->type)) {
            $document->errors()->push($error);
        }

        return $next($document);
    }

    /**
     * @param $value
     * @return Error|null
     */
    private function accept($value): ?Error
    {
        if (!is_string($value)) {
            return $this->translator->memberNotString('/data', 'type');
        }

        if (empty($value)) {
            return $this->translator->memberEmpty('/data', 'type');
        }

        if ($this->expects !== $value) {
            return $this->translator->resourceTypeNotSupported($value);
        }

        return null;
    }
}
