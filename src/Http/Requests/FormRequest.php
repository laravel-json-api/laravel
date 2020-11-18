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

namespace LaravelJsonApi\Laravel\Http\Requests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest as BaseFormRequest;
use LaravelJsonApi\Contracts\Schema\Schema;
use LaravelJsonApi\Core\JsonApiService;
use LaravelJsonApi\Validation\Factory as ValidationFactory;

class FormRequest extends BaseFormRequest
{

    /**
     * @var string
     */
    protected const JSON_API_MEDIA_TYPE = 'application/vnd.api+json';

    /**
     * @return bool
     */
    public function wantsJsonApi(): bool
    {
        $acceptable = $this->getAcceptableContentTypes();

        return isset($acceptable[0]) && self::JSON_API_MEDIA_TYPE === $acceptable[0];
    }

    /**
     * @return bool
     */
    public function acceptsJsonApi(): bool
    {
        return $this->accepts(self::JSON_API_MEDIA_TYPE);
    }

    /**
     * Determine if the request is sending JSON API content.
     *
     * @return bool
     */
    public function isJsonApi(): bool
    {
        return $this->matchesType(self::JSON_API_MEDIA_TYPE, $this->header('CONTENT_TYPE'));
    }

    /**
     * Get the JSON API schema for the request.
     *
     * @return Schema
     */
    public function schema(): Schema
    {
        return $this->jsonApi()->route()->schema();
    }

    /**
     * Get the model that the request relates to, if the URL has a resource id.
     *
     * @return Model|object|null
     */
    public function model(): ?object
    {
        $route = $this->jsonApi()->route();

        if ($route->hasResourceId()) {
            return $route->model();
        }

        return null;
    }

    /**
     * @return ValidationFactory
     */
    protected function validationErrors(): ValidationFactory
    {
        return $this->container->make(ValidationFactory::class);
    }

    /**
     * @return JsonApiService
     */
    protected function jsonApi(): JsonApiService
    {
        return $this->container->make(JsonApiService::class);
    }
}
