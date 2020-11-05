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

namespace LaravelJsonApi\Routing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\Route as IlluminateRoute;
use Illuminate\Support\Traits\ForwardsCalls;
use LogicException;

/**
 * Class Route
 *
 * @mixin IlluminateRoute
 */
class Route
{

    public const RESOURCE_TYPE = 'resource_type';
    public const RESOURCE_ID_NAME = 'resource_id_name';

    use ForwardsCalls;

    /**
     * @var IlluminateRoute
     */
    private IlluminateRoute $route;

    /**
     * Route constructor.
     *
     * @param IlluminateRoute $route
     */
    public function __construct(IlluminateRoute $route)
    {
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
     * Get the resource type.
     *
     * @return string
     */
    public function resourceType(): string
    {
        if ($type = $this->route->parameter(self::RESOURCE_TYPE)) {
            return $type;
        }

        throw new LogicException('No JSON API resource type set on route.');
    }

    /**
     * Get the resource id or the model (if bindings have been substituted).
     *
     * @return Model|mixed|string|null
     */
    public function modelOrResourceId()
    {
        if (!$name = $this->route->parameter(self::RESOURCE_ID_NAME)) {
            throw new LogicException('No JSON API resource id name set on route.');
        }

        if ($modelOrResourceId = $this->route->parameter($name)) {
            return $modelOrResourceId;
        }

        throw new LogicException('No JSON API resource id set on route.');
    }
}
