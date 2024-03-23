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
        return new PendingServerRegistration(
            $this->router,
            $this->servers->once($name),
        );
    }
}
