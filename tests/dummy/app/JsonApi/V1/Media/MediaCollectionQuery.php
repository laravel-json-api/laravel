<?php
/*
 * Copyright 2024 Cloud Creativity Limited
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

namespace App\JsonApi\V1\Media;

use LaravelJsonApi\Laravel\Http\Requests\ResourceQuery;
use LaravelJsonApi\Validation\Rule as JsonApiRule;

class MediaCollectionQuery extends ResourceQuery
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'fields' => [
                'nullable',
                'array',
                JsonApiRule::fieldSets(),
            ],
            'filter' => [
                'nullable',
                'array',
                JsonApiRule::filter(['id']),
            ],
            'include' => [
                'nullable',
                'string',
                JsonApiRule::includePathsForPolymorph(),
            ],
            'page' => [
                'nullable',
                'array',
                JsonApiRule::notSupported(),
            ],
            'sort' => [
                'nullable',
                'string',
                JsonApiRule::sort(['id']),
            ],
            'withCount' => [
                'nullable',
                'string',
                JsonApiRule::countableForPolymorph(),
            ],
        ];
    }
}
