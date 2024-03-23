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
use Illuminate\Support\Arr;
use LaravelJsonApi\Contracts\Server\Server;

class PendingServerRegistration
{

    /**
     * @var RegistrarContract
     */
    private RegistrarContract $router;

    /**
     * @var Server
     */
    private Server $server;

    /**
     * @var array
     */
    private array $attributes;

    /**
     * PendingServerRegistration constructor.
     *
     * @param RegistrarContract $router
     * @param Server $server
     */
    public function __construct(RegistrarContract $router, Server $server)
    {
        $this->router = $router;
        $this->server = $server;
        $this->attributes = [
            'middleware' => "jsonapi:{$server->name()}",
            'as' => "{$server->name()}.",
        ];
    }

    /**
     * @param string $name
     * @return $this
     */
    public function name(string $name): self
    {
        $this->attributes['as'] = $name;

        return $this;
    }

    /**
     * @param string $prefix
     * @return $this
     */
    public function prefix(string $prefix): self
    {
        $this->attributes['prefix'] = $prefix;

        return $this;
    }

    /**
     * Add middleware.
     *
     * @param string ...$middleware
     * @return $this
     */
    public function middleware(string ...$middleware): self
    {
        $this->attributes['middleware'] = array_merge(
            Arr::wrap($this->attributes['middleware'] ?? []),
            $middleware
        );

        return $this;
    }

    /**
     * Specify middleware that should be removed from the resource routes.
     *
     * @param string ...$middleware
     * @return $this
     */
    public function withoutMiddleware(string ...$middleware)
    {
        $this->attributes['excluded_middleware'] = array_merge(
            (array) ($this->options['excluded_middleware'] ?? []),
            $middleware
        );

        return $this;
    }

    /**
     * @param string $domain
     * @return $this
     */
    public function domain(string $domain): self
    {
        $this->attributes['domain'] = $domain;

        return $this;
    }

    /**
     * @param string $namespace
     * @return $this
     */
    public function namespace(string $namespace): self
    {
        $this->attributes['namespace'] = $namespace;

        return $this;
    }

    /**
     * Register server resources.
     *
     * @param \Closure $callback
     * @return void
     */
    public function resources(\Closure $callback): void
    {
        $this->router->group($this->attributes, function () use ($callback) {
            $callback(new ResourceRegistrar(
                $this->router,
                $this->server
            ), $this->router);
        });
    }
}
