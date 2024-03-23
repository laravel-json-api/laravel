<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace App\JsonApi\V1\Videos;

use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;
use LaravelJsonApi\Validation\Rule as JsonApiRule;

class VideoRequest extends ResourceRequest
{

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'id' => ['nullable', JsonApiRule::clientId()],
            'tags' => JsonApiRule::toMany(),
            'title' => ['required', 'string'],
            'url' => ['required', 'string'],
        ];
    }

}
