<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace LaravelJsonApi\Laravel\Http\Requests;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Foundation\Http\FormRequest as BaseFormRequest;
use Illuminate\Support\Str;
use LaravelJsonApi\Contracts\Schema\Schema;
use LaravelJsonApi\Core\JsonApiService;
use LaravelJsonApi\Validation\Factory as ValidationFactory;

class FormRequest extends BaseFormRequest
{

    /**
     * @var string
     */
    public const JSON_API_MEDIA_TYPE = 'application/vnd.api+json';

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
     * Is this a request to view any resource? (Index action.)
     *
     * @return bool
     */
    public function isViewingAny(): bool
    {
        return $this->isMethod('GET') && $this->doesntHaveResourceId() && $this->isNotRelationship();
    }

    /**
     * Is this a request to view a specific resource? (Read action.)
     *
     * @return bool
     */
    public function isViewingOne(): bool
    {
        return $this->isMethod('GET') && $this->hasResourceId() && $this->isNotRelationship();
    }

    /**
     * Is this a request to view related resources in a relationship? (Show-related action.)
     *
     * @return bool
     */
    public function isViewingRelated(): bool
    {
        return $this->isMethod('GET') && $this->isRelationship() && !$this->urlHasRelationships();
    }

    /**
     * Is this a request to view resource identifiers in a relationship? (Show-relationship action.)
     *
     * @return bool
     */
    public function isViewingRelationship(): bool
    {
        return $this->isMethod('GET') && $this->isRelationship() && $this->urlHasRelationships();
    }

    /**
     * Is this a request to create a resource?
     *
     * @return bool
     */
    public function isCreating(): bool
    {
        return $this->isMethod('POST') && $this->isNotRelationship();
    }

    /**
     * Is this a request to update a resource?
     *
     * @return bool
     */
    public function isUpdating(): bool
    {
        return $this->isMethod('PATCH') && $this->isNotRelationship();
    }

    /**
     * Is this a request to create or update a resource?
     *
     * @return bool
     */
    public function isCreatingOrUpdating(): bool
    {
        return $this->isCreating() || $this->isUpdating();
    }

    /**
     * Is this a request to replace a resource relationship?
     *
     * @return bool
     */
    public function isUpdatingRelationship(): bool
    {
        return $this->isMethod('PATCH') && $this->isRelationship();
    }

    /**
     * Is this a request to attach records to a resource relationship?
     *
     * @return bool
     */
    public function isAttachingRelationship(): bool
    {
        return $this->isMethod('POST') && $this->isRelationship();
    }

    /**
     * Is this a request to detach records from a resource relationship?
     *
     * @return bool
     */
    public function isDetachingRelationship(): bool
    {
        return $this->isMethod('DELETE') && $this->isRelationship();
    }

    /**
     * Is this a request to modify a resource relationship?
     *
     * @return bool
     */
    public function isModifyingRelationship(): bool
    {
        return $this->isUpdatingRelationship() ||
            $this->isAttachingRelationship() ||
            $this->isDetachingRelationship();
    }

    /**
     * @return bool
     */
    public function isDeleting(): bool
    {
        return $this->isMethod('DELETE') && $this->isNotRelationship();
    }

    /**
     * Is this a request to view or modify a relationship?
     *
     * @return bool
     */
    public function isRelationship(): bool
    {
        return $this->jsonApi()->route()->hasRelation();
    }

    /**
     * Is this a request to not view a relationship?
     *
     * @return bool
     */
    public function isNotRelationship(): bool
    {
        return !$this->isRelationship();
    }

    /**
     * Get the field name for a relationship request.
     *
     * @return string|null
     */
    public function getFieldName(): ?string
    {
        $route = $this->jsonApi()->route();

        if ($route->hasRelation()) {
            return $route->fieldName();
        }

        return null;
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
     * @return bool
     */
    protected function passesAuthorization()
    {
        try {
            /**
             * If the developer has implemented the `authorize` method, we
             * will return the result if it is a boolean. This allows
             * the developer to return a null value to indicate they want
             * the default authorization to run.
             */
            if (method_exists($this, 'authorize')) {
                $result = $this->container->call([$this, 'authorize']);
                if ($result !== null) {
                    return $result instanceof Response ? $result->authorize() : $result;
                }
            }

            /**
             * If the developer has not authorized the request themselves,
             * we run our default authorization as long as authorization is
             * enabled for both the server and the schema (checked via the
             * `mustAuthorize()` method).
             */
            if (method_exists($this, 'authorizeResource')) {
                $result = $this->container->call([$this, 'authorizeResource']);
                return $result instanceof Response ? $result->authorize() : $result;
            }

        } catch (AuthorizationException $ex) {
            $this->failIfUnauthenticated();
            throw $ex;
        }
        return true;
    }

    protected function failIfUnauthenticated()
    {
         /** @var Guard $auth */
        $auth = $this->container->make(Guard::class);

        if ($auth->guest()) {
            throw new AuthenticationException();
        }
    }

    /**
     * @inheritDoc
     */
    protected function failedAuthorization()
    {
        $this->failIfUnauthenticated();

        parent::failedAuthorization();
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
     * Is there a resource id?
     *
     * @return bool
     */
    private function hasResourceId(): bool
    {
        return $this->jsonApi()->route()->hasResourceId();
    }

    /**
     * Is the request not for a specific resource?
     *
     * @return bool
     */
    private function doesntHaveResourceId(): bool
    {
        return !$this->hasResourceId();
    }

    /**
     * Does the URL contain the keyword "relationships".
     *
     * @return bool
     */
    private function urlHasRelationships(): bool
    {
        return Str::of($this->url())->contains('relationships');
    }
}
