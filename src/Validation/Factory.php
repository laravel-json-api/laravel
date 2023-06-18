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
use LaravelJsonApi\Contracts\Validation\QueryManyValidator;
use LaravelJsonApi\Contracts\Validation\QueryOneValidator;
use LaravelJsonApi\Contracts\Validation\StoreValidator;
use LaravelJsonApi\Core\Document\Input\Values\ResourceType;
use LaravelJsonApi\Core\Document\ResourceObject;
use LaravelJsonApi\Core\Extensions\Atomic\Operations\Store;
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
                    throw new \RuntimeException('Not expecting resource query to throw.');
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
                    throw new \RuntimeException('Not expecting resource query to throw.');
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

            public function extract(Store $operation): array
            {
                $resource = ResourceObject::fromArray(
                    $operation->data->toArray()
                );

                return $resource->all();
            }

            public function make(?Request $request, Store $operation): Validator
            {
                try {
                    $resource = ResourceRequest::forResource($this->type->value);
                } catch (\Throwable $ex) {
                    throw new \RuntimeException('Not expecting resource query to throw.');
                }

                return $resource->makeValidator(
                    $this->extract($operation),
                );
            }
        };
    }
}
