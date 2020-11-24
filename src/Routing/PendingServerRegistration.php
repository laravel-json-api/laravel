<?php
/*
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
            ));
        });
    }
}
