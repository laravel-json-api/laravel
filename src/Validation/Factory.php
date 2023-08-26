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

namespace LaravelJsonApi\Laravel\Validation;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Request;
use LaravelJsonApi\Contracts\Validation\DestroyValidator;
use LaravelJsonApi\Contracts\Validation\QueryManyValidator;
use LaravelJsonApi\Contracts\Validation\QueryOneValidator;
use LaravelJsonApi\Contracts\Validation\RelationshipValidator;
use LaravelJsonApi\Contracts\Validation\StoreValidator;
use LaravelJsonApi\Contracts\Validation\UpdateValidator;
use LaravelJsonApi\Core\Document\ResourceObject;
use LaravelJsonApi\Core\Extensions\Atomic\Operations\Create;
use LaravelJsonApi\Core\Extensions\Atomic\Operations\Delete;
use LaravelJsonApi\Core\Extensions\Atomic\Operations\Update;
use LaravelJsonApi\Core\Extensions\Atomic\Operations\UpdateToMany;
use LaravelJsonApi\Core\Extensions\Atomic\Operations\UpdateToOne;
use LaravelJsonApi\Core\Values\ResourceType;
use LaravelJsonApi\Laravel\Http\Requests\ResourceQuery;
use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;

class Factory implements \LaravelJsonApi\Contracts\Validation\Factory
{
    /**
     * Factory constructor
     *
     * @param ResourceType $type
     */
    public function __construct(private readonly ResourceType $type)
    {
    }

    /**
     * @return QueryManyValidator
     */
    public function queryMany(): QueryManyValidator
    {
        return new class($this->type) implements QueryManyValidator {
            public function __construct(private readonly ResourceType $type)
            {
            }

            public function forRequest(Request $request): Validator
            {
                return $this->make($request, (array) $request->query());
            }

            public function make(?Request $request, array $parameters): Validator
            {
                try {
                    $query = ResourceQuery::queryMany($this->type->value);
                } catch (\Throwable $ex) {
                    throw new \RuntimeException('Not expecting resource query to throw.', 0, $ex);
                }

                return $query->makeValidator($parameters);
            }
        };
    }

    /**
     * @inheritDoc
     */
    public function queryOne(): QueryOneValidator
    {
        return new class($this->type) implements QueryOneValidator {
            public function __construct(private readonly ResourceType $type)
            {
            }

            public function forRequest(Request $request): Validator
            {
                return $this->make($request, (array) $request->query());
            }

            public function make(?Request $request, array $parameters): Validator
            {
                try {
                    $query = ResourceQuery::queryOne($this->type->value);
                } catch (\Throwable $ex) {
                    throw new \RuntimeException('Not expecting resource query to throw.', 0, $ex);
                }

                return $query->makeValidator($parameters);
            }
        };
    }

    /**
     * @inheritDoc
     */
    public function store(): StoreValidator
    {
        return new class($this->type) implements StoreValidator {
            public function __construct(private readonly ResourceType $type)
            {
            }

            public function extract(Create $operation): array
            {
                $resource = ResourceObject::fromArray(
                    $operation->data->toArray()
                );

                return $resource->all();
            }

            public function make(?Request $request, Create $operation): Validator
            {
                try {
                    $resource = ResourceRequest::forResource($this->type->value);
                } catch (\Throwable $ex) {
                    throw new \RuntimeException('Not expecting resource request to throw.', 0, $ex);
                }

                return $resource->makeValidator(
                    $this->extract($operation),
                );
            }
        };
    }

    /**
     * @inheritDoc
     */
    public function update(): UpdateValidator
    {
        return new class($this->type) implements UpdateValidator {
            private ?ResourceRequest $resource = null;
            public function __construct(private readonly ResourceType $type)
            {
            }

            public function extract(object $model, Update $operation): array
            {
                $resource = $this->resource();

                $document = $resource->json()->all();
                $existing = $resource->extractForUpdate($model);

                if (method_exists($resource, 'withExisting')) {
                    $existing = $resource->withExisting($model, $existing) ?? $existing;
                }

                return ResourceObject::fromArray($existing)->merge(
                    $document['data']
                )->all();
            }

            public function make(?Request $request, object $model, Update $operation): Validator
            {
                $resource = $this->resource();

                return $resource->makeValidator(
                    $this->extract($model, $operation),
                );
            }

            private function resource(): ResourceRequest
            {
                if ($this->resource) {
                    return $this->resource;
                }

                try {
                    return $this->resource = ResourceRequest::forResource($this->type->value);
                } catch (\Throwable $ex) {
                    throw new \RuntimeException('Not expecting resource request to throw.', 0, $ex);
                }
            }
        };
    }

    /**
     * @inheritDoc
     */
    public function destroy(): ?DestroyValidator
    {
        return new class($this->type) implements DestroyValidator {
            private ?ResourceRequest $resource = null;

            public function __construct(private readonly ResourceType $type)
            {
            }

            public function extract(object $model, Delete $operation): array
            {
                $resource = $this->resource();
                $document = $resource->extractForUpdate($model);

                if (method_exists($resource, 'metaForDelete')) {
                    $document['meta'] = (array) $resource->metaForDelete($model);
                }

                $fields = ResourceObject::fromArray($document)->all();
                $fields['meta'] = array_merge($fields['meta'] ?? [], $document['meta'] ?? []);

                return $fields;
            }

            public function make(?Request $request, object $model, Delete $operation): Validator
            {
                $resource = $this->resource();

                return $resource->createDeleteValidator(
                    $this->extract($model, $operation),
                );
            }

            private function resource(): ResourceRequest
            {
                if ($this->resource) {
                    return $this->resource;
                }

                try {
                    return $this->resource = ResourceRequest::forResource($this->type->value);
                } catch (\Throwable $ex) {
                    throw new \RuntimeException('Not expecting resource request to throw.', 0, $ex);
                }
            }
        };
    }

    /**
     * @inheritDoc
     */
    public function relation(): RelationshipValidator
    {
        return new class($this->type) implements RelationshipValidator {
            private ?ResourceRequest $resource = null;

            public function __construct(private readonly ResourceType $type)
            {
            }

            /**
             * @inheritDoc
             */
            public function extract(object $model, UpdateToOne|UpdateToMany $operation): array
            {
                $resource = $this->resource();

                $document = $resource->dataForRelationship(
                    $model,
                    $operation->getFieldName(),
                    ['data' => $operation->data?->toArray()],
                );

                return ResourceObject::fromArray($document)->all();
            }

            /**
             * @inheritDoc
             */
            public function make(?Request $request, object $model, UpdateToOne|UpdateToMany $operation): Validator
            {
                $resource = $this->resource();

                return $resource->createRelationshipValidator(
                    $operation->getFieldName(),
                    $this->extract($model, $operation),
                );
            }

            private function resource(): ResourceRequest
            {
                if ($this->resource) {
                    return $this->resource;
                }

                try {
                    return $this->resource = ResourceRequest::forResource($this->type->value);
                } catch (\Throwable $ex) {
                    throw new \RuntimeException('Not expecting resource request to throw.', 0, $ex);
                }
            }
        };
    }
}
