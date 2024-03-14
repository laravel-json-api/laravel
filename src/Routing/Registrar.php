<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
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
