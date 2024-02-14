<?php
/*
 * Copyright 2024 Cloud Creativity Limited
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

namespace LaravelJsonApi\Laravel\Routing;

use Illuminate\Contracts\Routing\Registrar as RegistrarContract;
use LaravelJsonApi\Contracts\Server\Repository;
use LaravelJsonApi\Core\Server\ServerRepository;

class Registrar
{

    /**
     * @var RegistrarContract
     */
    private RegistrarContract $router;

    /**
     * @var Repository
     */
    private Repository $servers;

    /**
     * Registrar constructor.
     *
     * @param RegistrarContract $router
     * @param Repository $servers
     */
    public function __construct(RegistrarContract $router, Repository $servers)
    {
        $this->router = $router;
        $this->servers = $servers;
    }

    /**
     * Register routes for the named JSON API server.
     *
     * @param string $name
     * @return PendingServerRegistration
     */
    public function server(string $name): PendingServerRegistration
    {
        // TODO add the `once` method to the server repository interface
        $server = match(true) {
            $this->servers instanceof ServerRepository => $this->servers->once($name),
            default => $this->servers->server($name),
        };

        return new PendingServerRegistration(
            $this->router,
            $server,
        );
    }
}
