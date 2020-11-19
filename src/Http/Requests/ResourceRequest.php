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

use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;
use LaravelJsonApi\Contracts\Resources\Container as ResourceContainer;
use LaravelJsonApi\Core\Document\Error;
use LaravelJsonApi\Core\Document\ErrorList;
use LaravelJsonApi\Core\Document\ResourceObject;
use LaravelJsonApi\Core\Exceptions\JsonApiException;
use LaravelJsonApi\Core\Resources\JsonApiResource;
use LaravelJsonApi\Core\Support\Str;
use LaravelJsonApi\Spec\RelationBuilder;
use LaravelJsonApi\Spec\ResourceBuilder;
use LaravelJsonApi\Spec\UnexpectedDocumentException;
use LogicException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use function array_key_exists;

class ResourceRequest extends FormRequest
{

    /**
     * @var callable|null
     */
    private static $requestResolver;

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
        $resolver = self::$requestResolver ?: new RequestResolver('Request');

        return $resolver($resourceType);
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
     * Is this a request to replace a resource relationship?
     *
     * @return bool
     */
    public function isUpdatingRelation(): bool
    {
        return $this->isMethod('PATCH') && $this->isRelationship();
    }

    /**
     * Is this a request to attach records to a resource relationship?
     *
     * @return bool
     */
    public function isAttachingRelation(): bool
    {
        return $this->isMethod('POST') && $this->isRelationship();
    }

    /**
     * Is this a request to detach records from a resource relationship?
     *
     * @return bool
     */
    public function isDetachingRelation(): bool
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
        return $this->isUpdatingRelation() || $this->isAttachingRelation() || $this->isDetachingRelation();
    }

    /**
     * Perform resource authorization.
     *
     * @param Gate $gate
     * @return bool
     */
    public function authorizeResource(Gate $gate): bool
    {
        if ($this->isCreating()) {
            return $gate->check('create', $this->schema()->model());
        }

        if ($this->isUpdating()) {
            return $gate->check('update', $this->modelOrFail());
        }

        if ($this->isModifyingRelationship()) {
            $related = new RelatedResourceRetriever(
                $this->jsonApi()->server(),
                $relation = $this->jsonApi()->route()->relation(),
                $this
            );

            if ($this->isAttachingRelation()) {
                $ability = 'attach';
            } else if ($this->isDetachingRelation()) {
                $ability = 'detach';
            } else {
                $ability = 'update';
            }

            return $gate->check(
                $ability . Str::classify($relation->name()),
                [$this->modelOrFail(), $related]
            );
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

        if (!is_array($document) || !isset($document['data']) || !is_array($document['data'])) {
            throw new LogicException('Expecting JSON API specification compliance to have been run.');
        }

        return $document;
    }

    /**
     * Get the field name for a relationship request.
     *
     * @return string
     */
    public function fieldName(): string
    {
        return $this->jsonApi()->route()->fieldName();
    }

    /**
     * @inheritDoc
     */
    public function validationData()
    {
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

        return ResourceObject::fromArray($data)->all();
    }

    /**
     * Get the validation data for a modify relationship request.
     *
     * @return array
     */
    public function validationDataForRelationship(): array
    {
        $document = $this->dataForRelationship(
            $this->model(),
            $this->jsonApi()->route()->fieldName(),
            $this->document()
        );

        return ResourceObject::fromArray($document)->all();
    }

    /**
     * Get the relationship value that has been validated.
     *
     * @return mixed
     */
    public function validatedForRelation()
    {
        $data = $this->validated();
        $fieldName = $this->fieldName();

        if (array_key_exists($fieldName, $data)) {
            return $data[$fieldName];
        }

        throw new LogicException(sprintf(
            'Expecting relation %s to have a rule so that it is validated.',
            $fieldName
        ));
    }

    /**
     * @inheritDoc
     */
    protected function prepareForValidation()
    {
        /** Content negotiation. */
        if (!$this->isSupportedMediaType()) {
            throw $this->unsupportedMediaType();
        }

        /** JSON API spec compliance. */
        if ($this->isCreating() || $this->isUpdating()) {
            $this->validateResourceDocument();
        } else if ($this->isModifyingRelationship()) {
            $this->validateRelationshipDocument();
        }
    }

    /**
     * @inheritDoc
     */
    protected function failedValidation(Validator $validator)
    {
        throw new JsonApiException($this->validationErrors()->createErrorsForResource(
            $this->schema(),
            $validator
        ));
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
     * @todo add translation
     */
    protected function unsupportedMediaType(): HttpExceptionInterface
    {
        return new HttpException(
            Response::HTTP_UNSUPPORTED_MEDIA_TYPE,
            'The request entity has a media type which the server or resource does not support.'
        );
    }

    /**
     * Get an exception if the JSON is invalid.
     *
     * @param \JsonException $ex
     * @return HttpExceptionInterface
     * @todo add translation
     */
    protected function invalidJson(\JsonException $ex): HttpExceptionInterface
    {
        return new JsonApiException(Error::make()
            ->setStatus(Response::HTTP_BAD_REQUEST)
            ->setCode($ex->getCode())
            ->setTitle('Invalid JSON')
            ->setDetail($ex->getMessage())
        );
    }

    /**
     * Get an exception if the JSON is not an object.
     *
     * @param UnexpectedDocumentException $ex
     * @return HttpExceptionInterface
     * @todo add translation
     */
    protected function unexpectedDocument(UnexpectedDocumentException $ex): HttpExceptionInterface
    {
        return new JsonApiException(Error::make()
            ->setStatus(Response::HTTP_BAD_REQUEST)
            ->setTitle('Invalid JSON')
            ->setDetail($ex->getMessage())
        );
    }

    /**
     * Get an exception for a JSON document that has failed JSON-API specification validation.
     *
     * @param ErrorList $errors
     * @return HttpExceptionInterface
     * @todo add translation
     */
    protected function invalidDocument(ErrorList $errors): HttpExceptionInterface
    {
        return new JsonApiException($errors);
    }

    /**
     * @inheritDoc
     */
    protected function createDefaultValidator(ValidationFactory $factory)
    {
        if ($this->isRelationship()) {
            return $this->createRelationshipValidator($factory);
        }

        return parent::createDefaultValidator($factory);
    }

    /**
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
        );
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
     * @param Model|object $record
     *      the record being updated.
     * @param array $document
     *      the JSON API document to validate.
     * @return array
     */
    protected function dataForUpdate(object $record, array $document): array
    {
        $data = $document['data'] ?? [];

        if ($this->mustValidateExisting($record, $data)) {
            $data['attributes'] = $this->extractAttributes(
                $record,
                $data['attributes'] ?? []
            );

            $data['relationships'] = $this->extractRelationships(
                $record,
                $data['relationships'] ?? []
            );
        }

        return $data;
    }

    /**
     * Get validation data for modifying a relationship.
     *
     * @param mixed $record
     * @param string $fieldName
     * @param array $document
     * @return array
     */
    protected function dataForRelationship(object $record, string $fieldName, array $document): array
    {
        $resource = $this->resources()->create($record);

        return [
            'type' => $resource->type(),
            'id' => $resource->id(),
            'relationships' => [
                $fieldName => [
                    'data' => $document['data'],
                ],
            ],
        ];
    }

    /**
     * Should existing resource values be provided to the validator for an update request?
     *
     * Child classes can overload this method if they need to programmatically work out
     * if existing values must be provided to the validator instance for an update request.
     *
     * @param Model|object $model
     *      the model being updated
     * @param array $document
     *      the JSON API document provided by the client.
     * @return bool
     */
    protected function mustValidateExisting(object $model, array $document): bool
    {
        return false !== $this->validateExisting;
    }

    /**
     * Extract attributes for a resource update.
     *
     * @param Model|object $model
     * @param array $new
     * @return array
     */
    protected function extractAttributes(object $model, array $new): array
    {
        return collect($this->existingAttributes($model))
            ->merge($new)
            ->all();
    }

    /**
     * Get any existing attributes for the provided model.
     *
     * @param Model|object $model
     * @return iterable
     */
    protected function existingAttributes(object $model): iterable
    {
        $resource = $this->resources()->create($model);

        return $resource->attributes();
    }

    /**
     * Extract relationships for a resource update.
     *
     * @param Model|object $model
     * @param array $new
     * @return array
     */
    protected function extractRelationships(object $model, array $new): array
    {
        return collect($this->existingRelationships($model))
            ->map(fn($value) => $this->convertExistingRelationships($value))
            ->merge($new)
            ->all();
    }

    /**
     * Get any existing relationships for the provided record.
     *
     * As there is no reliable way for us to work this out (as we do not
     * know the relationship keys), child classes should overload this method
     * to add existing relationship data.
     *
     * @param Model|object $model
     * @return iterable
     */
    protected function existingRelationships(object $model): iterable
    {
        return [];
    }

    /**
     * Get validation rules for a specified relationship field.
     *
     * @return array
     */
    private function relationshipRules(): array
    {
        $rules = $this->container->call([$this, 'rules']);
        $fieldName = $this->fieldName();

        return collect($rules)
            ->filter(fn($v, $key) => Str::startsWith($key, $fieldName))
            ->all();
    }

    /**
     * Convert relationships returned by the `existingRelationships()` method.
     *
     * We support the method returning JSON API formatted relationships, e.g.:
     *
     * ```
     * return [
     *          'author' => [
     *            'data' => [
     *              'type' => 'users',
     *              'id' => (string) $record->author->getRouteKey(),
     *          ],
     *      ],
     * ];
     * ```
     *
     * Or this shorthand:
     *
     * ```php
     * return [
     *      'author' => $record->author,
     * ];
     * ```
     *
     * This method converts the shorthand into the JSON API formatted relationships.
     *
     * @param $value
     * @return array
     */
    private function convertExistingRelationships($value)
    {
        if (is_array($value) && array_key_exists('data', $value)) {
            return $value;
        }

        if (is_null($value)) {
            return ['data' => null];
        }

        $value = $this->resources()->resolve($value);

        if ($value instanceof JsonApiResource) {
            return [
                'data' => [
                    'type' => $value->type(),
                    'id' => $value->id(),
                ],
            ];
        }

        $data = collect($value)
            ->map(fn(JsonApiResource $resource) => ['type' => $resource->type(), 'id' => $resource->id()])
            ->all();

        return compact('data');
    }

    /**
     * Validate the JSON API document for a resource request.
     *
     * @return void
     * @throws HttpExceptionInterface
     */
    private function validateResourceDocument(): void
    {
        $route = $this->jsonApi()->route();
        $id = $route->hasResourceId() ? $route->resourceId() : null;

        /** @var ResourceBuilder $builder */
        $builder = $this->container->make(ResourceBuilder::class);

        try {
            $document = $builder
                ->expects($route->resourceType(), $id)
                ->build($this->getContent());
        } catch (\JsonException $ex) {
            throw $this->invalidJson($ex);
        } catch (UnexpectedDocumentException $ex) {
            throw $this->unexpectedDocument($ex);
        }

        if ($document->invalid()) {
            throw $this->invalidDocument($document->errors());
        }
    }

    /**
     * Validate the JSON API document for a modify relationship request.
     *
     * @return void
     * @throws HttpExceptionInterface
     */
    private function validateRelationshipDocument(): void
    {
        $route = $this->jsonApi()->route();

        /** @var RelationBuilder $builder */
        $builder = $this->container->make(RelationBuilder::class);

        try {
            $document = $builder
                ->expects($route->resourceType(), $route->fieldName())
                ->build($this->getContent());
        } catch (\JsonException $ex) {
            throw $this->invalidJson($ex);
        } catch (UnexpectedDocumentException $ex) {
            throw $this->unexpectedDocument($ex);
        }

        if ($document->invalid()) {
            throw $this->invalidDocument($document->errors());
        }
    }

    /**
     * @return ResourceContainer
     */
    final protected function resources(): ResourceContainer
    {
        return $this->jsonApi()->server()->resources();
    }

}
