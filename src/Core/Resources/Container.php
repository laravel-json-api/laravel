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

namespace LaravelJsonApi\Core\Resources;

use Generator;
use InvalidArgumentException;
use LaravelJsonApi\Core\Contracts\Resources\Container as ContainerContract;
use LaravelJsonApi\Core\Contracts\Resources\Factory;
use LogicException;
use function get_class;
use function is_iterable;
use function is_object;
use function sprintf;

class Container implements ContainerContract
{

    /**
     * @var array
     */
    private array $bindings;

    /**
     * Container constructor.
     *
     * @param Factory ...$factories
     */
    public function __construct(Factory ...$factories)
    {
        foreach ($factories as $factory) {
            $this->attach($factory);
        }
    }

    /**
     * Attach a factory to the container.
     *
     * @param Factory $factory
     * @return void
     */
    public function attach(Factory $factory): void
    {
        foreach ($factory->handles() as $fqn) {
            $this->bindings[$fqn] = $factory;
        }
    }

    /**
     * @inheritDoc
     */
    public function resolve($value)
    {
        if ($value instanceof JsonApiResource) {
            return $value;
        }

        if (is_object($value) && $this->exists($value)) {
            return $this->create($value);
        }

        if (is_iterable($value)) {
            return $this->cursor($value);
        }

        throw new LogicException(sprintf(
            'Unable to resolve %s to a resource object. Check your resource configuration.',
            is_object($value) ? get_class($value) : 'non-object value'
        ));
    }

    /**
     * @inheritDoc
     */
    public function exists($record): bool
    {
        return isset($this->bindings[get_class($record)]);
    }

    /**
     * @inheritDoc
     */
    public function create($record): JsonApiResource
    {
        return $this->factoryFor($record)->createResource(
            $record
        );
    }

    /**
     * @inheritDoc
     */
    public function cursor(iterable $records): Generator
    {
        foreach ($records as $record) {
            if ($record instanceof JsonApiResource) {
                yield $record;
                continue;
            }

            yield $this->create($record);
        }
    }

    /**
     * @param mixed $record
     * @return Factory
     */
    private function factoryFor($record): Factory
    {
        if (!is_object($record)) {
            throw new InvalidArgumentException('Expecting record to be an object.');
        }

        if ($binding = $this->bindings[get_class($record)] ?? null) {
            return $binding;
        }

        throw new LogicException(sprintf(
            'Class %s does not have a resource object factory registered.',
            get_class($record)
        ));
    }

}
