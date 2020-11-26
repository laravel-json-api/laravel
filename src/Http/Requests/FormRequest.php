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

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest as BaseFormRequest;
use LaravelJsonApi\Contracts\Schema\Schema;
use LaravelJsonApi\Core\JsonApiService;
use LaravelJsonApi\Validation\Factory as ValidationFactory;
use LogicException;

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
     * Get the model that the request relates to, or fail if there is none.
     *
     * @return Model|object
     */
    public function modelOrFail(): object
    {
        if ($model = $this->model()) {
            return $model;
        }

        throw new LogicException('No model exists for this route.');
    }

    /**
     * @return bool
     */
    protected function passesAuthorization()
    {
        /**
         * If the developer has implemented the `authorize` method, we
         * will return the result if it is a boolean. This allows
         * the developer to return a null value to indicate they want
         * the default authorization to run.
         */
        if (method_exists($this, 'authorize')) {
            if (is_bool($passes = $this->container->call([$this, 'authorize']))) {
                return $passes;
            }
        }

        /**
         * If the developer has not authorized the request themselves,
         * we run our default authorization as long as authorization is
         * enabled for both the server and the schema (checked via the
         * `mustAuthorize()` method).
         */
        if (method_exists($this, 'authorizeResource')) {
            return $this->container->call([$this, 'authorizeResource']);
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    protected function failedAuthorization()
    {
        /** @var Guard $auth */
        $auth = $this->container->make(Guard::class);

        if ($auth->guest()) {
            throw new AuthenticationException();
        }

        throw new AuthorizationException;
    }

    /**
     * @return ValidationFactory
     */
    final protected function validationErrors(): ValidationFactory
    {
        return $this->container->make(ValidationFactory::class);
    }

    /**
     * @return JsonApiService
     */
    final protected function jsonApi(): JsonApiService
    {
        return $this->container->make(JsonApiService::class);
    }

    /**
     * Is the request for a specific resource?
     *
     * @return bool
     */
    final protected function isResource(): bool
    {
        return $this->jsonApi()->route()->hasResourceId();
    }

    /**
     * Is the request not for a specific resource?
     *
     * @return bool
     */
    final protected function isNotResource(): bool
    {
        return !$this->isResource();
    }

    /**
     * Is this a request to modify a relationship?
     *
     * @return bool
     */
    final protected function isRelationship(): bool
    {
        return $this->jsonApi()->route()->hasRelation();
    }

    /**
     * @return bool
     */
    final protected function isNotRelationship(): bool
    {
        return !$this->isRelationship();
    }
}
