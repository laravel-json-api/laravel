<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
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
