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

class RelationshipValidator
{

    /**
     * @var Translator
     */
    private Translator $translator;

    /**
     * RelationshipValidator constructor.
     *
     * @param Translator $translator
     */
    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Validate each relationship within the `/data/relationships` member.
     *
     * @param Document $document
     * @param \Closure $next
     * @return Document
     */
    public function validate(Document $document, \Closure $next): Document
    {
        $relationships = $document->get('data.relationships');

        if (is_object($relationships)) {
            foreach ($relationships as $field => $value) {
                if ('type' === $field || 'id' === $field) {
                    $document->errors()->push(
                        $this->translator->memberFieldNotAllowed('/data', 'relationships', $field)
                    );
                    continue;
                }

                if ($errors = $this->accept($field, $value)) {
                    $document->errors()->push(...$errors);
                }
            }
        }

        return $next($document);
    }

    /**
     * @param string $field
     * @param $value
     * @return array|null
     */
    private function accept(string $field, $value): ?array
    {
        if (!is_object($value)) {
            return [$this->translator->memberNotObject('/data/relationships', $field)];
        }

        if (!property_exists($value, 'data')) {
            return [$this->translator->memberRequired("/data/relationships/{$field}", 'data')];
        }

        $data = $value->data;

        if (is_array($data)) {
            return $this->acceptToMany($field, $data);
        }

        return $this->acceptToOne($field, $data);
    }

    /**
     * @param $field
     * @param $value
     * @return array|null
     */
    private function acceptToMany($field, $value): ?array
    {
        $path = "/data/relationships/{$field}/data";

        return collect($value)
            ->map(fn($value, $idx) => $this->acceptIdentifier($path, $value, $idx))
            ->flatten()
            ->all();
    }

    /**
     * @param $field
     * @param $value
     * @return array|null
     */
    private function acceptToOne($field, $value): ?array
    {
        $path = "/data/relationships/{$field}";

        if (!is_null($value) && !is_object($value)) {
            return [$this->translator->memberNotObject($path, 'data')];
        }

        if (is_object($value)) {
            return $this->acceptIdentifier($path, $value);
        }

        return null;
    }

    /**
     * @param $path
     * @param $value
     * @param int|null $idx
     * @return array
     */
    private function acceptIdentifier($path, $value, int $idx = null): array
    {
        $member = is_int($idx) ? strval($idx) : 'data';

        if (!is_object($value)) {
            return [$this->translator->memberNotObject($path, $member)];
        }

        $errors = [];
        $dataPath = sprintf('%s/%s', rtrim($path, '/'), $member);

        $errors[] = $this->acceptIdentifierType($dataPath, $value);
        $errors[] = $this->acceptIdentifierId($dataPath, $value);

        return array_filter($errors);
    }

    /**
     * @param string $path
     * @param object $identifier
     * @return Error|null
     */
    private function acceptIdentifierType(string $path, object $identifier): ?Error
    {
        if (!property_exists($identifier, 'type')) {
            return $this->translator->memberRequired($path, 'type');
        }

        if (!is_string($identifier->type)) {
            return $this->translator->memberNotString($path, 'type');
        }

        if (empty($identifier->type)) {
            return $this->translator->memberEmpty($path, 'type');
        }

        return null;
    }

    /**
     * @param string $path
     * @param object $identifier
     * @return Error|null
     */
    private function acceptIdentifierId(string $path, object $identifier): ?Error
    {
        if (!property_exists($identifier, 'id')) {
            return $this->translator->memberRequired($path, 'id');
        }

        if (!is_string($identifier->id)) {
            return $this->translator->memberNotString($path, 'id');
        }

        if (empty($identifier->id) && '0' !== $identifier->id) {
            return $this->translator->memberEmpty($path, 'id');
        }

        return null;
    }
}
