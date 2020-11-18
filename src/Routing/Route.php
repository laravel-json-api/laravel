<?php
/**
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

use Illuminate\Routing\Route as IlluminateRoute;
use Illuminate\Support\Traits\ForwardsCalls;
use LaravelJsonApi\Contracts\Routing\Route as RouteContract;
use LaravelJsonApi\Contracts\Schema\Relation;
use LaravelJsonApi\Contracts\Schema\Schema;
use LaravelJsonApi\Contracts\Server\Server;
use LogicException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Route implements RouteContract
{

    public const RESOURCE_TYPE = 'resource_type';
    public const RESOURCE_ID_NAME = 'resource_id_name';
    public const RESOURCE_RELATIONSHIP = 'resource_relationship';

    use ForwardsCalls;

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
     * @param Server $server
     * @param IlluminateRoute $route
     */
    public function __construct(Server $server, IlluminateRoute $route)
    {
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

        if ($modelOrResourceId = $this->route->parameter($name)) {
            return $modelOrResourceId;
        }

        throw new LogicException('No JSON API resource id set on route.');
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
        if ($this->hasResourceId()) {
            $this->setModel($this->schema()->repository()->find(
                $this->resourceId()
            ));
        }
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
     * @return string|null
     */
    private function resourceIdName(): ?string
    {
        return $this->route->parameter(self::RESOURCE_ID_NAME) ?: null;
    }

}
