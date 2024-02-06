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
use LaravelJsonApi\Contracts\Schema\Schema;
use LaravelJsonApi\Core\Support\Str;

class RelationshipRegistrar
{

    /**
     * @var RegistrarContract
     */
    private RegistrarContract $router;

    /**
     * @var Schema
     */
    private Schema $schema;

    /**
     * @var string
     */
    private string $resourceType;

    /**
     * @var string
     */
    private string $controller;

    /**
     * @var string
     */
    private string $parameter;

    /**
     * RelationshipRegistrar constructor.
     *
     * @param RegistrarContract $router
     * @param Schema $schema
     * @param string $resourceType
     * @param string $controller
     * @param string $parameter
     */
    public function __construct(
        RegistrarContract $router,
        Schema $schema,
        string $resourceType,
        string $controller,
        string $parameter
    ) {
        $this->router = $router;
        $this->schema = $schema;
        $this->resourceType = $resourceType;
        $this->controller = $controller;
        $this->parameter = $parameter;
    }

    /**
     * @param string $fieldName
     * @param bool $hasMany
     * @param array $options
     * @return RouteCollection
     */
    public function register(string $fieldName, bool $hasMany, array $options = []): RouteCollection
    {
        $routes = new RouteCollection();

        foreach ($this->getRelationMethods($hasMany, $options) as $method) {
            $fn = 'add' . ucfirst($method);
            $route = $this->{$fn}($fieldName, $options);
            $routes->add($route);
        }

        return $routes;
    }

    /**
     * Add the read related action.
     *
     * @param string $fieldName
     * @param array $options
     * @return IlluminateRoute
     */
    protected function addShowRelated(string $fieldName, array $options): IlluminateRoute
    {
        $uri = $this->getRelationshipUri($fieldName);
        $action = $this->getRelationshipAction(
            'showRelated',
            'showRelated' . Str::classify($fieldName),
            $fieldName,
            $options
        );

        $route = $this->router->get($uri, $action);
        $route->defaults(Route::RESOURCE_TYPE, $this->resourceType);
        $route->defaults(Route::RESOURCE_ID_NAME, $this->parameter);
        $route->defaults(Route::RESOURCE_RELATIONSHIP, $fieldName);

        return $route;
    }

    /**
     * Add the read relationship action.
     *
     * @param string $fieldName
     * @param array $options
     * @return IlluminateRoute
     */
    protected function addShowRelationship(string $fieldName, array $options): IlluminateRoute
    {
        $uri = $this->getRelationshipUri($fieldName);
        $action = $this->getRelationshipAction(
            'showRelationship',
            'show' . Str::classify($fieldName),
            "{$fieldName}.show",
            $options
        );

        $route = $this->router->get("relationships/{$uri}", $action);
        $route->defaults(Route::RESOURCE_TYPE, $this->resourceType);
        $route->defaults(Route::RESOURCE_ID_NAME, $this->parameter);
        $route->defaults(Route::RESOURCE_RELATIONSHIP, $fieldName);

        return $route;
    }

    /**
     * Add the update relationship action.
     *
     * @param string $fieldName
     * @param array $options
     * @return IlluminateRoute
     */
    protected function addUpdateRelationship(string $fieldName, array $options): IlluminateRoute
    {
        $uri = $this->getRelationshipUri($fieldName);
        $action = $this->getRelationshipAction(
            'updateRelationship',
            'update' . Str::classify($fieldName),
            "{$fieldName}.update",
            $options
        );

        $route = $this->router->patch("relationships/{$uri}", $action);
        $route->defaults(Route::RESOURCE_TYPE, $this->resourceType);
        $route->defaults(Route::RESOURCE_ID_NAME, $this->parameter);
        $route->defaults(Route::RESOURCE_RELATIONSHIP, $fieldName);

        return $route;
    }

    /**
     * Add the attach relationship action.
     *
     * @param string $fieldName
     * @param array $options
     * @return IlluminateRoute
     */
    protected function addAttachRelationship(string $fieldName, array $options): IlluminateRoute
    {
        $uri = $this->getRelationshipUri($fieldName);
        $action = $this->getRelationshipAction(
            'attachRelationship',
            'attach' . Str::classify($fieldName),
            "{$fieldName}.attach",
            $options
        );

        $route = $this->router->post("relationships/{$uri}", $action);
        $route->defaults(Route::RESOURCE_TYPE, $this->resourceType);
        $route->defaults(Route::RESOURCE_ID_NAME, $this->parameter);
        $route->defaults(Route::RESOURCE_RELATIONSHIP, $fieldName);

        return $route;
    }

    /**
     * Add the detach relationship action.
     *
     * @param string $fieldName
     * @param array $options
     * @return IlluminateRoute
     */
    protected function addDetachRelationship(string $fieldName, array $options): IlluminateRoute
    {
        $uri = $this->getRelationshipUri($fieldName);
        $action = $this->getRelationshipAction(
            'detachRelationship',
            'detach' . Str::classify($fieldName),
            "{$fieldName}.detach",
            $options
        );

        $route = $this->router->delete("relationships/{$uri}", $action);
        $route->defaults(Route::RESOURCE_TYPE, $this->resourceType);
        $route->defaults(Route::RESOURCE_ID_NAME, $this->parameter);
        $route->defaults(Route::RESOURCE_RELATIONSHIP, $fieldName);

        return $route;
    }

    /**
     * @param bool $hasMany
     * @param array $options
     * @return string[]
     */
    private function getRelationMethods(bool $hasMany, array $options): array
    {
        $methods = [
            'showRelated',
            'showRelationship',
            'updateRelationship',
        ];

        if ($hasMany) {
            $methods = array_merge($methods, [
                'attachRelationship',
                'detachRelationship',
            ]);
        }

        if (isset($options['only'])) {
            $methods = array_intersect($methods, (array) $options['only']);
        }

        if (isset($options['except'])) {
            $methods = array_diff($methods, (array) $options['except']);
        }

        return $methods;
    }

    /**
     * @param string $method
     * @param string $specificMethod
     * @param string $defaultName
     * @param array $options
     * @return array
     */
    private function getRelationshipAction(
        string $method,
        string $specificMethod,
        string $defaultName,
        array $options
    ): array
    {
        if (in_array($method, $options['relationship_own_actions'] ?? [], true)) {
            $method = $specificMethod;
        }

        $name = $this->getRelationRouteName($method, $defaultName, $options);

        $action = ['as' => $name, 'uses' => $this->controller.'@'.$method];

        if (isset($options['middleware'])) {
            $action['middleware'] = $options['middleware'];
        }
        
        if (isset($options['excluded_middleware'])) {
            $action['excluded_middleware'] = $options['excluded_middleware'];
        }
        if (isset($options['route_action_middleware'])) {
            /** @var \closure<string[], string, string, string>|array<string, string|string[]> $routeMiddlewareMap */
            $routeMiddlewareMap = $options['route_action_middleware'];
            /** @var string|string[] $routeActionMiddleware */
            $routeActionMiddleware = is_callable($routeMiddlewareMap) ? $routeMiddlewareMap(
                Str::classify($this->resourceType),
                Str::classify($method),
                Str::classify($defaultName)
            ) : ($routeMiddlewareMap[$method] ?? []);
            /** @var string[] $newMiddleware */
            $newMiddleware = is_array($routeActionMiddleware) ? $routeActionMiddleware : [$routeActionMiddleware];
            /** @var string[] $oldMiddleware */
            $oldMiddleware = $action['middleware'] ?? [];

            // dd($oldMiddleware, $routeActionMiddleware);
            $action['middleware'] = array_merge($oldMiddleware, $newMiddleware);
        }

        return $action;
    }

    /**
     * @param string $fieldName
     * @return string
     */
    private function getRelationshipUri(string $fieldName): string
    {
        return $this->schema->relationship($fieldName)->uriName();
    }

    /**
     * Get the route name.
     *
     * @param string $method
     * @param string $default
     * @param array $options
     * @return string
     */
    protected function getRelationRouteName(string $method, string $default, array $options): string
    {
        $custom = $options['names'] ?? [];

        return $custom[$method] ?? $default;
    }

}
