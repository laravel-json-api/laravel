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

namespace LaravelJsonApi\Http\Requests;

use Illuminate\Http\Response;
use LaravelJsonApi\Core\Contracts\Query\QueryParameters;
use LaravelJsonApi\Core\Query\FieldSets;
use LaravelJsonApi\Core\Query\IncludePaths;
use LaravelJsonApi\Core\Query\SortFields;
use Symfony\Component\HttpKernel\Exception\HttpException;
use function array_key_exists;

class ResourceQuery extends FormRequest implements QueryParameters
{

    /**
     * @var string[]
     */
    protected array $mediaTypes = [
        'application/vnd.api+json',
    ];

    /**
     * @return array
     */
    public function validationData()
    {
        return $this->query();
    }

    /**
     * @inheritDoc
     */
    public function includePaths(): ?IncludePaths
    {
        $data = $this->validated();

        if (array_key_exists('include', $data)) {
            return IncludePaths::fromString($data['include'] ?: '');
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function sparseFieldSets(): ?FieldSets
    {
        $data = $this->validated();

        if (array_key_exists('fields', $data)) {
            return FieldSets::fromArray($data['fields']);
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function sortFields(): ?SortFields
    {
        $data = $this->validated();

        if (array_key_exists('sort', $data)) {
            return SortFields::fromString($data['sort'] ?: '');
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function page(): ?array
    {
        $data = $this->validated();

        if (array_key_exists('page', $data)) {
            return $data['page'];
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function filter(): ?array
    {
        $data = $this->validated();

        if (array_key_exists('filter', $data)) {
            return $data['filter'];
        }

        return null;
    }

    /**
     * @return void
     */
    protected function prepareForValidation()
    {
        if (!$this->accepts($this->mediaTypes())) {
            throw $this->notAcceptable();
        }
    }

    /**
     * @return string[]
     */
    protected function mediaTypes(): array
    {
        return $this->mediaTypes;
    }

    /**
     * @return HttpException
     */
    protected function notAcceptable(): HttpException
    {
        return new HttpException(
            Response::HTTP_NOT_ACCEPTABLE,
            "The requested resource is capable of generating only content not acceptable "
            . "according to the Accept headers sent in the request."
        );
    }
}
