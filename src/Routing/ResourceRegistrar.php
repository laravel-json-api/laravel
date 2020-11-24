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
use Illuminate\Routing\Route as IlluminateRoute;
use Illuminate\Routing\RouteCollection;
use LaravelJsonApi\Contracts\Server\Server;
use LaravelJsonApi\Core\Support\Str;

class ResourceRegistrar
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
     * ResourceRegistrar constructor.
     *
     * @param RegistrarContract $router
     * @param Server $server
     */
    public function __construct(RegistrarContract $router, Server $server)
    {
        $this->router = $router;
        $this->server = $server;
    }

    /**
     * @param string $resourceType
     * @param string|null $controller
     * @return PendingResourceRegistration
     */
    public function resource(string $resourceType, string $controller = null): PendingResourceRegistration
    {
        return new PendingResourceRegistration(
            $this,
            $resourceType,
            $controller ?: $this->guessController($resourceType)
        );
    }

    /**
     * @param string $resourceType
     * @param string $controller
     * @param array $options
     * @param \Closure $callback
     * @return RouteCollection
     */
    public function relationships(string $resourceType, string $controller, array $options, \Closure $callback)
    {
        $parameter = $this->getResourceParameterName($resourceType, $options);
        $attributes = $this->getRelationshipsAction($resourceType, $parameter, $options);

        $registrar = new RelationshipRegistrar(
            $this->router,
            $resourceType,
            $controller,
            $parameter
        );

        $routes = new RouteCollection();

        $this->router->group($attributes, function () use ($registrar, $callback, $routes) {
            $relationships = new Relationships($registrar);

            $callback($relationships);

            foreach ($relationships->register() as $route) {
                $routes->add($route);
            }
        });

        return $routes;
    }

    /**
     * Register resource routes.
     *
     * @param string $resourceType
     * @param string $controller
     * @param array $options
     * @return RouteCollection
     */
    public function register(string $resourceType, string $controller, array $options = []): RouteCollection
    {
        $routes = new RouteCollection();

        foreach ($this->getResourceMethods($options) as $method) {
            $fn = 'addResource' . ucfirst($method);
            $route = $this->{$fn}($resourceType, $controller, $options);
            $routes->add($route);
        }

        return $routes;
    }

    /**
     * Add the index method.
     *
     * @param string $resourceType
     * @param string $controller
     * @param array $options
     * @return IlluminateRoute
     */
    protected function addResourceIndex(string $resourceType, string $controller, array $options): IlluminateRoute
    {
        $uri = $this->getResourceUri($resourceType, $options);
        $action = $this->getResourceAction($resourceType, $controller, 'index', null, $options);

        $route = $this->router->get($uri, $action);
        $route->defaults(Route::RESOURCE_TYPE, $resourceType);

        return $route;
    }

    /**
     * Add the store method.
     *
     * @param string $resourceType
     * @param string $controller
     * @param array $options
     * @return IlluminateRoute
     */
    protected function addResourceStore(string $resourceType, string $controller, array $options): IlluminateRoute
    {
        $uri = $this->getResourceUri($resourceType, $options);
        $action = $this->getResourceAction($resourceType, $controller, 'store', null, $options);

        $route = $this->router->post($uri, $action);
        $route->defaults(Route::RESOURCE_TYPE, $resourceType);

        return $route;
    }

    /**
     * Add the read method.
     *
     * @param string $resourceType
     * @param string $controller
     * @param array $options
     * @return IlluminateRoute
     */
    protected function addResourceShow(string $resourceType, string $controller, array $options): IlluminateRoute
    {
        $parameter = $this->getResourceParameterName($resourceType, $options);
        $uri = $this->getResourceUri($resourceType, $options);
        $action = $this->getResourceAction($resourceType, $controller, 'show', $parameter, $options);

        $route = $this->router->get(sprintf('%s/{%s}', $uri, $parameter), $action);
        $route->defaults(Route::RESOURCE_TYPE, $resourceType);
        $route->defaults(Route::RESOURCE_ID_NAME, $parameter);

        return $route;
    }

    /**
     * Add the update method.
     *
     * @param string $resourceType
     * @param string $controller
     * @param array $options
     * @return IlluminateRoute
     */
    protected function addResourceUpdate(string $resourceType, string $controller, array $options): IlluminateRoute
    {
        $parameter = $this->getResourceParameterName($resourceType, $options);
        $uri = $this->getResourceUri($resourceType, $options);
        $action = $this->getResourceAction($resourceType, $controller, 'update', $parameter, $options);

        $route = $this->router->patch(sprintf('%s/{%s}', $uri, $parameter), $action);
        $route->defaults(Route::RESOURCE_TYPE, $resourceType);
        $route->defaults(Route::RESOURCE_ID_NAME, $parameter);

        return $route;
    }

    /**
     * Add the destroy method.
     *
     * @param string $resourceType
     * @param string $controller
     * @param array $options
     * @return IlluminateRoute
     */
    protected function addResourceDestroy(string $resourceType, string $controller, array $options): IlluminateRoute
    {
        $parameter = $this->getResourceParameterName($resourceType, $options);
        $uri = $this->getResourceUri($resourceType, $options);
        $action = $this->getResourceAction($resourceType, $controller, 'destroy', $parameter, $options);

        $route = $this->router->delete(sprintf('%s/{%s}', $uri, $parameter), $action);
        $route->defaults(Route::RESOURCE_TYPE, $resourceType);
        $route->defaults(Route::RESOURCE_ID_NAME, $parameter);

        return $route;
    }

    /**
     * @param string $resourceType
     * @param array $options
     * @return string
     */
    private function getResourceUri(string $resourceType, array $options): string
    {
        if (isset($options['resource_uri'])) {
            return $options['resource_uri'];
        }

        return Str::dasherize($resourceType);
    }

    /**
     * @param string $resourceType
     * @param array $options
     * @return string
     */
    private function getResourceParameterName(string $resourceType, array $options): string
    {
        if (isset($options['parameter'])) {
            return $options['parameter'];
        }

        $param = Str::singular($resourceType);

        /**
         * Dash-case is not allowed for route parameters. Therefore if the
         * resource type contains a dash, we will underscore it.
         */
        if (Str::contains($param, '-')) {
            $param = Str::underscore($param);
        }

        return $param;
    }

    /**
     * Get the action array for a resource route.
     *
     * @param string $resourceType
     * @param string $controller
     * @param string $method
     * @param string|null $parameter
     * @param array $options
     * @return array
     */
    private function getResourceAction(
        string $resourceType,
        string $controller,
        string $method,
        ?string $parameter,
        array $options
    ) {
        $name = $this->getResourceRouteName($resourceType, $method, $options);

        $action = ['as' => $name, 'uses' => $controller.'@'.$method];

        if (isset($options['middleware'])) {
            $action['middleware'] = $options['middleware'];
        }

        if (isset($options['excluded_middleware'])) {
            $action['excluded_middleware'] = $options['excluded_middleware'];
        }

        $action['where'] = $this->getWheres($resourceType, $parameter, $options);

        return $action;
    }

    /**
     * Get the action array for the relationships group.
     *
     * @param string $resourceType
     * @param string|null $parameter
     * @param array $options
     * @return array
     */
    private function getRelationshipsAction(string $resourceType, ?string $parameter, array $options)
    {
        $uri = $this->getResourceUri($resourceType, $options);

        $action = [
            'prefix' => sprintf('%s/{%s}', $uri, $parameter),
            'as' => "{$resourceType}.",
        ];

        if (isset($options['middleware'])) {
            $action['middleware'] = $options['middleware'];
        }

        if (isset($options['excluded_middleware'])) {
            $action['excluded_middleware'] = $options['excluded_middleware'];
        }

        $action['where'] = $this->getWheres($resourceType, $parameter, $options);

        return $action;
    }

    /**
     * @param string $resourceType
     * @param string|null $parameter
     * @param array $options
     * @return array
     */
    private function getWheres(string $resourceType, ?string $parameter, array $options): array
    {
        $where = $options['wheres'] ?? [];

        if ($parameter && !isset($action['where'][$parameter])) {
            $where[$parameter] = $this->getIdPattern($resourceType);
        }

        return $where;
    }

    /**
     * @param string $resourceType
     * @return string
     */
    private function getIdPattern(string $resourceType): string
    {
        return $this->server
            ->schemas()
            ->schemaFor($resourceType)
            ->id()
            ->pattern();
    }

    /**
     * Get the route name.
     *
     * @param string $resourceType
     * @param string $method
     * @param array $options
     * @return string
     */
    protected function getResourceRouteName(string $resourceType, string $method, array $options): string
    {
        $custom = $options['names'] ?? [];

        return $custom[$method] ?? "{$resourceType}.{$method}";
    }

    /**
     * Get the applicable resource methods.
     *
     * @param  array  $options
     * @return array
     */
    private function getResourceMethods(array $options): array
    {
        $methods = [
            'index',
            'store',
            'show',
            'update',
            'destroy',
        ];

        if (isset($options['only'])) {
            $methods = array_intersect($methods, (array) $options['only']);
        }

        if (isset($options['except'])) {
            $methods = array_diff($methods, (array) $options['except']);
        }

        return $methods;
    }

    /**
     * Guess the controller name from the resource type.
     *
     * @param string $resourceType
     * @return string
     */
    private function guessController(string $resourceType): string
    {
        return Str::classify(Str::singular($resourceType)) . 'Controller';
    }
}
