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

use InvalidArgumentException;
use LaravelJsonApi\Core\Contracts\Resources\Factory as FactoryContract;
use LogicException;
use Throwable;
use function array_keys;
use function get_class;
use function is_object;
use function sprintf;

class Factory implements FactoryContract
{

    /**
     * @var array
     */
    private $bindings;

    /**
     * Factory constructor.
     *
     * @param iterable $bindings
     */
    public function __construct(iterable $bindings = [])
    {
        foreach ($bindings as $record => $resource) {
            $this->attach($record, $resource);
        }
    }

    /**
     * @param string $record
     *      the fully-qualified class name for the record.
     * @param string $resource
     *      the fully-qualified class name for the resource object.
     * @return void
     */
    public function attach(string $record, string $resource): void
    {
        $this->bindings[$record] = $resource;
    }

    /**
     * Attach
     *
     * @param iterable $bindings
     * @return void
     */
    public function attachAll(iterable $bindings): void
    {
        foreach ($bindings as $record => $resource) {
            $this->attach($record, $resource);
        }
    }

    /**
     * @inheritDoc
     */
    public function handles(): iterable
    {
        return array_keys($this->bindings);
    }

    /**
     * @inheritDoc
     */
    public function createResource($record): JsonApiResource
    {
        if (!is_object($record)) {
            throw new InvalidArgumentException('Expecting an object.');
        }

        $resource = $this->bindings[get_class($record)] ?? null;

        if (!$resource) {
            throw new LogicException(sprintf(
                'Unexpected record class - %s',
                get_class($record)
            ));
        }

        try {
            return $this->build($resource, $record);
        } catch (Throwable $ex) {
            throw new LogicException(sprintf(
                'Failed to build %s resource object for record %s.',
                $resource,
                get_class($record)
            ), 0, $ex);
        }
    }

    /**
     * Build a new resource object instance.
     *
     * @param string $fqn
     * @param $record
     * @return mixed
     */
    protected function build(string $fqn, $record)
    {
        return new $fqn($record);
    }

}
