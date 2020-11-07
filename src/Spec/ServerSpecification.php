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

namespace LaravelJsonApi\Spec;

use LaravelJsonApi\Contracts\Http\Server;

class ServerSpecification implements Specification
{

    /**
     * @var Server
     */
    private Server $server;

    /**
     * ServerSpecification constructor.
     *
     * @param Server $server
     */
    public function __construct(Server $server)
    {
        $this->server = $server;
    }

    /**
     * @inheritDoc
     */
    public function clientIds(string $resourceType): bool
    {
        // @TODO
        return false;
    }

    /**
     * @inheritDoc
     */
    public function exists(string $resourceType, string $resourceId): bool
    {
        return $this->server
            ->store()
            ->exists($resourceType, $resourceId);
    }

    /**
     * @inheritDoc
     */
    public function fields(string $resourceType): iterable
    {
        $schema = $this->server
            ->schemas()
            ->schemaFor($resourceType);

        yield from $schema->attributes();
        yield from $schema->relationships();
    }

    /**
     * @inheritDoc
     */
    public function types(): array
    {
        return $this->server
            ->schemas()
            ->types();
    }

}
