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

use Illuminate\Contracts\Container\Container;
use Illuminate\Routing\Route as IlluminateRoute;
use Illuminate\Support\Traits\ForwardsCalls;
use LaravelJsonApi\Contracts\Auth\Authorizer;
use LaravelJsonApi\Contracts\Routing\Route as RouteContract;
use LaravelJsonApi\Contracts\Schema\Relation;
use LaravelJsonApi\Contracts\Schema\Schema;
use LaravelJsonApi\Contracts\Server\Server;
use LaravelJsonApi\Core\Document\ResourceIdentifier;
use LogicException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Route implements RouteContract
{

    public const RESOURCE_TYPE = 'resource_type';
    public const RESOURCE_ID_NAME = 'resource_id_name';
    public const RESOURCE_RELATIONSHIP = 'resource_relationship';

    use ForwardsCalls;

    /**
     * @var Container
     */
    private Container $container;

    /**
     * @var Server
     */
    private Server $server;

    /**
     * @var IlluminateRoute
     */
    private IlluminateRoute $route;

    /**
     * Route constructor.
     *
     * @param Container $container
     * @param Server $server
     * @param IlluminateRoute $route
     */
    public function __construct(Container $container, Server $server, IlluminateRoute $route)
    {
        $this->container = $container;
        $this->server = $server;
        $this->route = $route;
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return $this->forwardCallTo($this->route, $name, $arguments);
    }

    /**
     * @inheritDoc
     */
    public function resourceType(): string
    {
        if ($type = $this->route->parameter(self::RESOURCE_TYPE)) {
            return $type;
        }

        throw new LogicException('No JSON API resource type set on route.');
    }

    /**
     * @inheritDoc
     */
    public function modelOrResourceId()
    {
        if (!$name = $this->resourceIdName()) {
            throw new LogicException('No JSON API resource id name set on route.');
        }

        $modelOrResourceId = $this->route->parameter($name);

        if (!is_object($modelOrResourceId) && ResourceIdentifier::idIsEmpty($modelOrResourceId)) {
            throw new LogicException('No JSON API resource id set on route.');
        }

        return $modelOrResourceId;
    }

    /**
     * @inheritDoc
     */
    public function resourceId(): string
    {
        $modelOrResourceId = $this->modelOrResourceId();

        if (is_object($modelOrResourceId)) {
            return $this->server
                ->resources()
                ->create($modelOrResourceId)
                ->id();
        }

        return $modelOrResourceId;
    }

    /**
     * @inheritDoc
     */
    public function hasResourceId(): bool
    {
        return !empty($this->resourceIdName());
    }

    /**
     * @inheritDoc
     */
    public function model(): object
    {
        $modelOrResourceId = $this->modelOrResourceId();

        if (is_object($modelOrResourceId)) {
            return $modelOrResourceId;
        }

        throw new LogicException('Expecting bindings to be substituted.');
    }

    /**
     * @inheritDoc
     */
    public function fieldName(): string
    {
        if ($name = $this->route->parameter(self::RESOURCE_RELATIONSHIP)) {
            return $name;
        }

        throw new LogicException('No JSON API relationship name set on route.');
    }

    /**
     * @inheritDoc
     */
    public function schema(): Schema
    {
        return $this->server->schemas()->schemaFor(
            $this->resourceType()
        );
    }

    /**
     * @inheritDoc
     */
    public function authorizer(): Authorizer
    {
        return $this->container->make(
            $this->schema()->authorizer()
        );
    }

    /**
     * @inheritDoc
     */
    public function hasRelation(): bool
    {
        return !!$this->route->parameter(self::RESOURCE_RELATIONSHIP);
    }

    /**
     * @inheritDoc
     */
    public function inverse(): Schema
    {
        return $this->server->schemas()->schemaFor(
            $this->relation()->inverse()
        );
    }

    /**
     * @inheritDoc
     */
    public function relation(): Relation
    {
        return $this->schema()->relationship(
            $this->fieldName()
        );
    }

    /**
     * @inheritDoc
     */
    public function substituteBindings(): void
    {
        if ($this->hasSubstitutedBindings()) {
            $this->checkBinding();
            return;
        }

        if ($this->hasResourceId()) {
            $this->setModel($this->schema()->repository()->find(
                $this->resourceId()
            ));
        }
    }

    /**
     * Has the model binding already been substituted?
     *
     * In a normal Laravel application setup, the `api` middleware group will
     * include Laravel's binding substitution middleware. This means that
     * typically the boot JSON:API middleware will run *after* bindings have been
     * substituted.
     *
     * If the route that is being executed has type-hinted the model, this means
     * the model will already be substituted into the route. For example, this
     * can occur if the developer has written their own controller action, or
     * for custom actions.
     *
     * @return bool
     */
    private function hasSubstitutedBindings(): bool
    {
        if ($name = $this->resourceIdName()) {
            $expected = $this->schema()->model();
            return $this->route->parameter($name) instanceof $expected;
        }

        return false;
    }

    /**
     * @param object|null $model
     * @return void
     * @throws NotFoundHttpException
     */
    private function setModel(?object $model): void
    {
        if ($model) {
            $this->route->setParameter(
                $this->resourceIdName(),
                $model
            );
            return;
        }

        throw new NotFoundHttpException();
    }

    /**
     * Check the model that has already been substituted.
     *
     * If Laravel has substituted bindings before the JSON:API binding substitution
     * is triggered, we need to check that the model that has been set on the route
     * by Laravel does exist in our API. This is because the API's existence logic
     * may not match the route binding query that Laravel executed to substitute
     * the binding. E.g. if the developer has applied global scopes in the Server's
     * `serving()` method, these global scopes may have been applied *after* the
     * binding was substituted.
     *
     * @return void
     */
    private function checkBinding(): void
    {
        $resourceId = $this->server->resources()->create(
            $this->model(),
        )->id();

        if (!$this->schema()->repository()->exists($resourceId)) {
            throw new NotFoundHttpException();
        }
    }

    /**
     * @return string|null
     */
    private function resourceIdName(): ?string
    {
        return $this->route->parameter(self::RESOURCE_ID_NAME) ?: null;
    }

}
