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
use LaravelJsonApi\Spec\Specification;
use LaravelJsonApi\Spec\Translator;

class ClientIdValidator
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
     * ClientIdValidator constructor.
     *
     * @param Specification $spec
     * @param Translator $translator
     */
    public function __construct(Specification $spec, Translator $translator)
    {
        $this->spec = $spec;
        $this->translator = $translator;
    }

    /**
     * Validate the `/data/id` member of the document.
     *
     * @param Document $document
     * @param \Closure $next
     * @return Document
     */
    public function validate(Document $document, \Closure $next): Document
    {
        if ($document->id()) {
            return $next($document);
        }

        $data = $document->data;

        if (!property_exists($data, 'id')) {
            return $next($document);
        }

        if (false === $this->spec->clientIds($document->type())) {
            $document->errors()->push(
                $this->translator->resourceDoesNotSupportClientIds($document->type())
            );
        }

        if ($error = $this->accept($data->id)) {
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
            return $this->translator->memberNotString('/data', 'id');
        }

        if (empty($value)) {
            return $this->translator->memberEmpty('/data', 'id');
        }

        return null;
    }
}
