<?php
/*
 * Copyright 2021 Cloud Creativity Limited
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

use Closure;
use Illuminate\Routing\RouteCollection;

class PendingResourceRegistration
{

    /**
     * @var ResourceRegistrar
     */
    private ResourceRegistrar $registrar;

    /**
     * @var string
     */
    private string $resourceType;

    /**
     * @var string
     */
    private string $controller;

    /**
     * @var array
     */
    private array $options;

    /**
     * @var bool
     */
    private bool $registered = false;

    /**
     * @var Closure|null
     */
    private ?Closure $relationships = null;

    /**
     * @var array|string[]
     */
    private array $map = [
        'create' => 'store',
        'read' => 'show',
        'delete' => 'destroy',
    ];

    /**
     * PendingResourceRegistration constructor.
     *
     * @param ResourceRegistrar $registrar
     * @param string $resourceType
     * @param string $controller
     */
    public function __construct(
        ResourceRegistrar $registrar,
        string $resourceType,
        string $controller
    ) {
        $this->registrar = $registrar;
        $this->resourceType = $resourceType;
        $this->controller = $controller;
        $this->options = [];
    }

    /**
     * Set the methods the controller should apply to.
     *
     * @param string ...$actions
     * @return $this
     */
    public function only(string ...$actions): self
    {
        $this->options['only'] = $this->normalizeActions($actions);

        return $this;
    }

    /**
     * Set the methods the controller should exclude.
     *
     * @param string ...$actions
     * @return $this
     */
    public function except(string ...$actions): self
    {
        $this->options['except'] = $this->normalizeActions($actions);

        return $this;
    }

    /**
     * Only register read-only actions.
     *
     * @return $this
     */
    public function readOnly(): self
    {
        return $this->only('index', 'show');
    }

    /**
     * Set the route names for controller actions.
     *
     * @param array $names
     * @return $this
     */
    public function names(array $names): self
    {
        foreach ($names as $method => $name) {
            $this->name($method, $name);
        }

        return $this;
    }

    /**
     * Set the route name for a controller action.
     *
     * @param string $method
     * @param string $name
     * @return $this
     */
    public function name(string $method, string $name): self
    {
        if (!isset($this->options['names'])) {
            $this->options['names'] = [];
        }

        $method = $this->map[$method] ?? $method;
        $this->options['names'][$method] = $name;

        return $this;
    }

    /**
     * Override the route parameter name.
     *
     * @param string $parameter
     * @return $this
     */
    public function parameter(string $parameter): self
    {
        $this->options['parameter'] = $parameter;

        return $this;
    }

    /**
     * Add middleware to the resource routes.
     *
     * @param string ...$middleware
     * @return $this
     */
    public function middleware(string ...$middleware): self
    {
        $this->options['middleware'] = $middleware;

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
        $this->options['excluded_middleware'] = array_merge(
            (array) ($this->options['excluded_middleware'] ?? []),
            $middleware
        );

        return $this;
    }

    /**
     * @param Closure $callback
     * @return $this
     */
    public function relationships(Closure $callback): self
    {
        $this->relationships = $callback;

        return $this;
    }

    /**
     * Register the resource routes.
     *
     * @return RouteCollection
     */
    public function register(): RouteCollection
    {
        $this->registered = true;

        $routes = $this->registrar->register(
            $this->resourceType,
            $this->controller,
            $this->options
        );

        if ($this->relationships) {
            $relations = $this->registrar->relationships(
                $this->resourceType,
                $this->controller,
                $this->options,
                $this->relationships
            );

            foreach ($relations as $route) {
                $routes->add($route);
            }
        }

        return $routes;
    }

    /**
     * Handle the object's destruction.
     *
     * @return void
     */
    public function __destruct()
    {
        if (!$this->registered) {
            $this->register();
        }
    }

    /**
     * @param array $actions
     * @return array
     */
    private function normalizeActions(array $actions): array
    {
        return collect($actions)
            ->map(fn($action) => $this->map[$action] ?? $action)
            ->all();
    }
}
