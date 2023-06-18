<?php
/*
 * Copyright 2023 Cloud Creativity Limited
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

use Illuminate\Foundation\Http\FormRequest as BaseFormRequest;
use Illuminate\Support\Str;
use LaravelJsonApi\Core\JsonApiService;

class JsonApiRequest extends BaseFormRequest
{
    /**
     * @var string
     */
    public const JSON_API_MEDIA_TYPE = 'application/vnd.api+json';

    /**
     * @return void
     */
    public function validateResolved(): void
    {
        // no-op
    }

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
