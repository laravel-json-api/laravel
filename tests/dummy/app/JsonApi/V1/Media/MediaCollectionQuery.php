<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
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
