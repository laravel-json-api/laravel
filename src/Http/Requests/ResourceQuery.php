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
use LaravelJsonApi\Core\Query\QueryParameters;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ResourceQuery extends FormRequest
{

    /**
     * @var string[]
     */
    protected $mediaTypes = [
        'application/vnd.api+json',
    ];

    /**
     * @return QueryParameters
     */
    public function jsonApiQuery(): QueryParameters
    {
        return QueryParameters::fromArray($this->validated());
    }

    /**
     * @return array
     */
    public function validationData()
    {
        return $this->query();
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
