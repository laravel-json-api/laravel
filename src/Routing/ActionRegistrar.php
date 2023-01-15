<?php
/*
 * Copyright 2023 Cloud Creativity Limited
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
use Illuminate\Routing\Route as IlluminateRoute;
use Illuminate\Routing\RouteCollection;
use LaravelJsonApi\Core\Support\Str;

class ActionRegistrar
{

    /**
     * @var RegistrarContract
     */
    private RegistrarContract $router;

    /**
     * @var ResourceRegistrar
     */
    private ResourceRegistrar $resource;

    /**
     * @var RouteCollection
     */
    private RouteCollection $routes;

    /**
     * @var string
     */
    private string $resourceType;

    /**
     * @var array
     */
    private array $options;

    /**
     * @var string
     */
    private string $controller;

    /**
     * @var string|null
     */
    private ?string $prefix;

    /**
     * @var bool
     */
    private bool $id = false;

    /**
     * ActionRegistrar constructor.
     *
     * @param RegistrarContract $router
     * @param ResourceRegistrar $resource
     * @param RouteCollection $routes
     * @param string $resourceType
     * @param array $options
     * @param string $controller
     * @param string|null $prefix
     */
    public function __construct(
        RegistrarContract $router,
        ResourceRegistrar $resource,
        RouteCollection $routes,
        string $resourceType,
        array $options,
        string $controller,
        string $prefix = null
    ) {
        $this->router = $router;
        $this->resource = $resource;
        $this->routes = $routes;
        $this->resourceType = $resourceType;
        $this->options = $options;
        $this->controller = $controller;
        $this->prefix = $prefix;
    }

    /**
     * @return $this
     */
    public function withId(): self
    {
        $copy = clone $this;
        $copy->id = true;

        return $copy;
    }

    /**
     * Register a new GET route.
     *
     * @param string $uri
     * @param string|null $method
     * @return ActionProxy
     */
    public function get(string $uri, string $method = null): ActionProxy
    {
        return $this->register('get', $uri, $method);
    }

    /**
     * Register a new POST route.
     *
     * @param string $uri
     * @param string|null $method
     * @return ActionProxy
     */
    public function post(string $uri, string $method = null): ActionProxy
    {
        return $this->register('post', $uri, $method);
    }

    /**
     * Register a new PATCH route.
     *
     * @param string $uri
     * @param string|null $method
     * @return ActionProxy
     */
    public function patch(string $uri, string $method = null): ActionProxy
    {
        return $this->register('patch', $uri, $method);
    }

    /**
     * Register a new PUT route.
     *
     * @param string $uri
     * @param string|null $method
     * @return ActionProxy
     */
    public function put(string $uri, string $method = null): ActionProxy
    {
        return $this->register('put', $uri, $method);
    }

    /**
     * Register a new DELETE route.
     *
     * @param string $uri
     * @param string|null $method
     * @return ActionProxy
     */
    public function delete(string $uri, string $method = null): ActionProxy
    {
        return $this->register('delete', $uri, $method);
    }

    /**
     * Register a new OPTIONS route.
     *
     * @param string $uri
     * @param string|null $method
     * @return ActionProxy
     */
    public function options(string $uri, string $method = null): ActionProxy
    {
        return $this->register('options', $uri, $method);
    }

    /**
     * @param string $method
     * @param string $uri
     * @param string|null $action
     * @return ActionProxy
     */
    public function register(string $method, string $uri, string $action = null): ActionProxy
    {
        $action = $action ?: $this->guessControllerAction($uri);
        $parameter = $this->getParameter();

        $route = $this->router->{$method}(
            $this->uri($uri, $parameter),
            sprintf('%s@%s', $this->controller, $action)
        );

        $this->route($route, $parameter);

        return new ActionProxy($route, $action);
    }

    /**
     * @return string|null
     */
    private function getParameter(): ?string
    {
        if ($this->id) {
            return $this->resource->getResourceParameterName(
                $this->resourceType,
                $this->options
            );
        }

        return null;
    }

    /**
     * Configure the supplied route.
     *
     * @param IlluminateRoute $route
     * @param string|null $parameter
     */
    private function route(IlluminateRoute $route, ?string $parameter): void
    {
        $route->where($this->resource->getWheres(
            $this->resourceType,
            $parameter,
            $this->options
        ));

        $route->defaults(Route::RESOURCE_TYPE, $this->resourceType);

        if ($parameter) {
            $route->defaults(Route::RESOURCE_ID_NAME, $parameter);
        }

        $this->routes->add($route);
    }

    /**
     * Normalize the URI.
     *
     * @param string $uri
     * @param string|null $parameter
     * @return string
     */
    private function uri(string $uri, ?string $parameter): string
    {
        $uri = ltrim($uri, '/');

        if ($this->prefix) {
            $uri = sprintf('%s/%s', $this->prefix, $uri);
        }

        if ($this->id) {
            return sprintf('{%s}/%s', $parameter, $uri);
        }

        return $uri;
    }

    /**
     * @param string $uri
     * @return string
     */
    private function guessControllerAction(string $uri): string
    {
        return Str::camel($uri);
    }
}
