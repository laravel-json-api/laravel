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

use Illuminate\Routing\RouteCollection;

class PendingRelationshipRegistration
{

    /**
     * @var RelationshipRegistrar
     */
    private RelationshipRegistrar $registrar;

    /**
     * @var string
     */
    private string $fieldName;

    /**
     * @var bool
     */
    private bool $hasMany;

    /**
     * @var array
     */
    private array $options;

    /**
     * @var bool
     */
    private bool $registered = false;

    /**
     * @var array
     */
    private array $map = [
        'related' => 'showRelated',
        'show' => 'showRelationship',
        'read' => 'showRelationship',
        'update' => 'updateRelationship',
        'replace' => 'updateRelationship',
        'attach' => 'attachRelationship',
        'add' => 'attachRelationship',
        'detach' => 'detachRelationship',
        'remove' => 'detachRelationship',
    ];

    /**
     * PendingRelationshipRegistration constructor.
     *
     * @param RelationshipRegistrar $registrar
     * @param string $fieldName
     * @param bool $hasMany
     */
    public function __construct(
        RelationshipRegistrar $registrar,
        string $fieldName,
        bool $hasMany
    ) {
        $this->registrar = $registrar;
        $this->fieldName = $fieldName;
        $this->hasMany = $hasMany;
        $this->options = [];
    }

    /**
     * Set the URI for the relationship, if it is not the same as the field name.
     *
     * @param string $uri
     * @return $this
     */
    public function uri(string $uri): self
    {
        $this->options['relationship_uri'] = $uri;

        return $this;
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
        return $this->only('related', 'show');
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
    public function withoutMiddleware(string ...$middleware): self
    {
        $this->options['excluded_middleware'] = array_merge(
            (array) ($this->options['excluded_middleware'] ?? []),
            $middleware
        );

        return $this;
    }

    /**
     * Specify that a specified action should have its own named action on the controller.
     *
     * @param string ...$actions
     * @return $this
     */
    public function ownAction(string ...$actions): self
    {
        $this->options['relationship_own_actions'] = collect($actions)
            ->map(fn($method) => $this->map[$method] ?? $method)
            ->all();

        return $this;
    }

    /**
     * Specify that all the relationship actions should have their own named action on the controller.
     *
     * @return $this
     */
    public function ownActions(): self
    {
        return $this->ownAction(
            'related',
            'show',
            'update',
            'attach',
            'detach',
        );
    }

    /**
     * Register the resource routes.
     *
     * @return RouteCollection
     */
    public function register(): RouteCollection
    {
        $this->registered = true;

        return $this->registrar->register(
            $this->fieldName,
            $this->hasMany,
            $this->options
        );
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
