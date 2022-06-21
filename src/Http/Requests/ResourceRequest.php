<?php
/*
 * Copyright 2022 Cloud Creativity Limited
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

use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use LaravelJsonApi\Contracts\Auth\Authorizer;
use LaravelJsonApi\Contracts\Schema\Relation;
use LaravelJsonApi\Core\Document\ResourceObject;
use LaravelJsonApi\Core\Exceptions\JsonApiException;
use LaravelJsonApi\Core\Query\IncludePaths;
use LaravelJsonApi\Core\Store\LazyRelation;
use LaravelJsonApi\Core\Support\Str;
use LaravelJsonApi\Laravel\Exceptions\HttpUnsupportedMediaTypeException;
use LaravelJsonApi\Spec\RelationBuilder;
use LaravelJsonApi\Spec\ResourceBuilder;
use LogicException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use function array_key_exists;

class ResourceRequest extends FormRequest
{

    /**
     * @var callable|null
     */
    private static $requestResolver;

    /**
     * @var array|null
     */
    private ?array $validationData = null;

    /**
     * @var LazyRelation|null
     */
    private ?LazyRelation $relation = null;

    /**
     * Specify the callback to use to guess the request class for a JSON API resource.
     *
     * @param callable $resolver
     * @return void
     */
    public static function guessResourceRequestUsing(callable $resolver): void
    {
        self::$requestResolver = $resolver;
    }

    /**
     * Resolve the request instance for the specified resource type.
     *
     * @param string $resourceType
     * @return ResourceRequest
     */
    public static function forResource(string $resourceType): ResourceRequest
    {
        $resolver = self::$requestResolver ?: new RequestResolver(RequestResolver::REQUEST);

        return $resolver($resourceType);
    }

    /**
     * Resolve the request instance for the specified resource type, if it exists.
     *
     * @param string $resourceType
     * @return ResourceRequest|null
     */
    public static function forResourceIfExists(string $resourceType): ?ResourceRequest
    {
        $resolver = self::$requestResolver ?: new RequestResolver(RequestResolver::REQUEST);

        return $resolver($resourceType, true);
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
     * Get the model referenced in a to-one relationship.
     *
     * @return Model|object|null
     */
    public function toOne(): ?object
    {
        if ($this->isModifyingRelationship()) {
            return $this->relation()->get();
        }

        throw new LogicException(
            'Can only retrieve the model for a to-one relationship when the relationship is being modified.'
        );
    }

    /**
     * Get the models referenced in a to-many relationship.
     *
     * @return Collection
     */
    public function toMany(): Collection
    {
        if ($this->isModifyingRelationship()) {
            return $this->relation()->collect();
        }

        throw new LogicException(
            'Can only retrieve the models for a to-many relationship when the relationship is being modified.'
        );
    }

    /**
     * Perform resource authorization.
     *
     * @param Authorizer $authorizer
     * @return bool
     */
    public function authorizeResource(Authorizer $authorizer): bool
    {
        if ($this->isCreating()) {
            return $authorizer->store(
                $this,
                $this->schema()->model(),
            );
        }

        if ($this->isUpdating()) {
            return $authorizer->update($this, $this->modelOrFail());
        }

        if ($this->isModifyingRelationship()) {
            $model = $this->modelOrFail();
            $fieldName = $this->jsonApi()->route()->fieldName();

            if ($this->isAttachingRelationship()) {
                return $authorizer->attachRelationship($this, $model, $fieldName);
            }

            if ($this->isDetachingRelationship()) {
                return $authorizer->detachRelationship($this, $model, $fieldName);
            }

            return $authorizer->updateRelationship($this, $model, $fieldName);
        }

        if ($this->isDeleting()) {
            return $authorizer->destroy($this, $this->modelOrFail());
        }

        return true;
    }

    /**
     * Get the decoded JSON API document.
     *
     * @return array
     */
    public function document(): array
    {
        $document = $this->json()->all();

        if (!is_array($document) || !array_key_exists('data', $document) || !(is_array($document['data']) || is_null($document['data']))) {
            throw new LogicException('Expecting JSON API specification compliance to have been run.');
        }

        return $document;
    }

    /**
     * @inheritDoc
     */
    public function validationData()
    {
        /**
         * We cache the validation data because the developer may access it in
         * their `rules` method. In that scenario, we do not want the data to
         * be calculated twice.
         */
        if (is_array($this->validationData)) {
            return $this->validationData;
        }

        $document = $this->document();

        if ($this->isCreating()) {
            $data = $this->dataForCreate($document);
        } else if ($this->isUpdating()) {
            $data = $this->dataForUpdate(
                $this->model(),
                $document
            );
        } else {
            $data = $document['data'];
        }

        return $this->validationData = ResourceObject::fromArray($data)->all();
    }

    /**
     * Get the validation data for a modify relationship request.
     *
     * @return array
     */
    public function validationDataForRelationship(): array
    {
        $document = $this->dataForRelationship(
            $this->modelOrFail(),
            $this->jsonApi()->route()->fieldName(),
            $this->document()
        );

        return ResourceObject::fromArray($document)->all();
    }

    /**
     * @return array
     */
    public function validationDataForDelete(): array
    {
        $document = $this->dataForDelete($this->modelOrFail());

        $fields = ResourceObject::fromArray($document)->all();
        $fields['meta'] = array_merge($fields['meta'] ?? [], $document['meta'] ?? []);

        return $fields;
    }

    /**
     * Get the relationship value that has been validated.
     *
     * @return mixed
     */
    public function validatedForRelation()
    {
        $data = $this->validated();
        $fieldName = $this->getFieldName();

        if (array_key_exists($fieldName, $data)) {
            return $data[$fieldName];
        }

        throw new LogicException(sprintf(
            'Expecting relation %s to have a rule so that it is validated.',
            $fieldName
        ));
    }

    /**
     * Prepare the data for validation.
     *
     * Before validating the data, we need to:
     *
     * 1. Ensure the media type is supported;
     * 2. Parse the document for compliance with the JSON:API spec.
     *
     * We only need to do this for the requests where we are expecting a JSON:API document,
     * i.e. resource create/update requests and requests to modify a resource's relationship.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        if ($this->isCreatingOrUpdating()) {
            $this->assertSupportedMediaType();
            $this->validateResourceDocument();
        } else if ($this->isModifyingRelationship()) {
            $this->assertSupportedMediaType();
            $this->validateRelationshipDocument();
        }
    }

    /**
     * @inheritDoc
     */
    protected function failedValidation(Validator $validator)
    {
        $factory = $this->validationErrors();

        if ($this->isDeleting()) {
            $errors = $factory->createErrorsForDeleteResource($validator);
        } else {
            $errors = $factory->createErrorsForResource(
                $this->schema(),
                $validator
            );
        }

        throw new JsonApiException($errors);
    }

    /**
     * @return bool
     */
    protected function isSupportedMediaType(): bool
    {
        return $this->isJsonApi();
    }

    /**
     * Get an exception if the media type is not supported.
     *
     * @return HttpExceptionInterface
     */
    protected function unsupportedMediaType(): HttpExceptionInterface
    {
        return new HttpUnsupportedMediaTypeException();
    }

    /**
     * @inheritDoc
     */
    protected function createDefaultValidator(ValidationFactory $factory)
    {
        if ($this->isDeleting()) {
            return $this->createDeleteValidator($factory);
        }

        if ($this->isRelationship()) {
            return $this->createRelationshipValidator($factory);
        }

        return parent::createDefaultValidator($factory);
    }

    /**
     * Create a validator to validate a relationship document.
     *
     * @param ValidationFactory $factory
     * @return Validator
     */
    protected function createRelationshipValidator(ValidationFactory $factory): Validator
    {
        return $factory->make(
            $this->validationDataForRelationship(),
            $this->relationshipRules(),
            $this->messages(),
            $this->attributes()
        )->stopOnFirstFailure($this->stopOnFirstFailure);
    }

    /**
     * Create a validator to validate a delete request.
     *
     * @param ValidationFactory $factory
     * @return Validator
     */
    protected function createDeleteValidator(ValidationFactory $factory): Validator
    {
        return $factory->make(
            $this->validationDataForDelete(),
            method_exists($this, 'deleteRules') ? $this->container->call([$this, 'deleteRules']) : [],
            array_merge(
                $this->messages(),
                method_exists($this, 'deleteMessages') ? $this->deleteMessages() : []
            ),
            array_merge(
                $this->attributes(),
                method_exists($this, 'deleteAttributes') ? $this->deleteAttributes() : []
            )
        )->stopOnFirstFailure($this->stopOnFirstFailure);
    }

    /**
     * Get validation data for creating a domain record.
     *
     * @param array $document
     * @return array
     */
    protected function dataForCreate(array $document): array
    {
        return $document['data'] ?? [];
    }

    /**
     * Get validation data for updating a domain record.
     *
     * The JSON API spec says:
     *
     * > If a request does not include all of the attributes for a resource,
     * > the server MUST interpret the missing attributes as if they were included
     * > with their current values. The server MUST NOT interpret missing
     * > attributes as null values.
     *
     * So that the validator has access to the current values of attributes, we
     * merge attributes provided by the client over the top of the existing attribute
     * values.
     *
     * @param Model|object $model
     *      the model being updated.
     * @param array $document
     *      the JSON:API document to validate.
     * @return array
     */
    protected function dataForUpdate(object $model, array $document): array
    {
        $existing = $this->extractForUpdate($model);

        if (method_exists($this, 'withExisting')) {
            $existing = $this->withExisting($model, $existing) ?? $existing;
        }

        return ResourceObject::fromArray($existing)->merge(
            $document['data']
        )->jsonSerialize();
    }

    /**
     * Get validation data for modifying a relationship.
     *
     * @param Model|object $model
     * @param string $fieldName
     * @param array $document
     * @return array
     */
    protected function dataForRelationship(object $model, string $fieldName, array $document): array
    {
        $route = $this->jsonApi()->route();

        return [
            'type' => $route->resourceType(),
            'id' => $route->resourceId(),
            'relationships' => [
                $fieldName => [
                    'data' => $document['data'],
                ],
            ],
        ];
    }

    /**
     * Get validation data for deleting a resource.
     *
     * @param Model|object $record
     * @return array
     */
    protected function dataForDelete(object $record): array
    {
        $data = $this->extractForUpdate($record);

        if (method_exists($this, 'metaForDelete')) {
            $data['meta'] = (array) $this->metaForDelete($record);
        }

        return $data;
    }

    /**
     * @throws HttpExceptionInterface
     */
    private function assertSupportedMediaType(): void
    {
        if (!$this->isSupportedMediaType()) {
            throw $this->unsupportedMediaType();
        }
    }

    /**
     * Extract the existing values for the provided model.
     *
     * @param object $model
     * @return array
     */
    private function extractForUpdate(object $model): array
    {
        $encoder = $this->jsonApi()->server()->encoder();

        return $encoder
            ->withRequest($this)
            ->withIncludePaths($this->includePathsToExtract($model))
            ->withResource($model)
            ->toArray()['data'];
    }

    /**
     * Get the relationship paths that must be included when extracting the current field values.
     *
     * @param object $model
     * @return IncludePaths
     */
    private function includePathsToExtract(object $model): IncludePaths
    {
        $paths = collect($this->schema()->relationships())
            ->filter(fn (Relation $relation) => $relation->isValidated())
            ->map(fn (Relation $relation) => $relation->name())
            ->values();

        return IncludePaths::fromArray($paths);
    }

    /**
     * Get validation rules for a specified relationship field.
     *
     * @return array
     */
    private function relationshipRules(): array
    {
        $rules = $this->container->call([$this, 'rules']);
        $fieldName = $this->getFieldName();

        return collect($rules)
            ->filter(fn($v, $key) => Str::startsWith($key, $fieldName))
            ->all();
    }

    /**
     * Validate the JSON API document for a resource request.
     *
     * @return void
     * @throws JsonApiException
     */
    private function validateResourceDocument(): void
    {
        $route = $this->jsonApi()->route();
        $id = $route->hasResourceId() ? $route->resourceId() : null;

        /** @var ResourceBuilder $builder */
        $builder = $this->container->make(ResourceBuilder::class);

        $document = $builder
            ->expects($route->resourceType(), $id)
            ->build($this->getContent());

        if ($document->invalid()) {
            throw new JsonApiException($document);
        }
    }

    /**
     * Validate the JSON API document for a modify relationship request.
     *
     * @return void
     * @throws JsonApiException
     */
    private function validateRelationshipDocument(): void
    {
        $route = $this->jsonApi()->route();

        /** @var RelationBuilder $builder */
        $builder = $this->container->make(RelationBuilder::class);

        $document = $builder
            ->expects($route->resourceType(), $route->fieldName())
            ->build($this->getContent());

        if ($document->invalid()) {
            throw new JsonApiException($document);
        }
    }

    /**
     * @return LazyRelation
     */
    private function relation(): LazyRelation
    {
        if ($this->relation) {
            return $this->relation;
        }

        $jsonApi = $this->jsonApi();

        return $this->relation = new LazyRelation(
            $jsonApi->server(),
            $jsonApi->route()->relation(),
            $this->json()->all(),
        );
    }

}
