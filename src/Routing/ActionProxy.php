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

use Illuminate\Routing\Route as IlluminateRoute;
use Illuminate\Support\Traits\ForwardsCalls;

/**
 * Class ActionProxy
 *
 * @mixin IlluminateRoute
 */
class ActionProxy
{

    use ForwardsCalls;

    /**
     * @var IlluminateRoute
     */
    private IlluminateRoute $route;

    /**
     * @var string
     */
    private string $controllerMethod;

    /**
     * @var bool
     */
    private bool $named = false;

    /**
     * ActionProxy constructor.
     *
     * @param IlluminateRoute $route
     * @param string $controllerMethod
     */
    public function __construct(IlluminateRoute $route, string $controllerMethod)
    {
        $this->route = $route;
        $this->controllerMethod = $controllerMethod;
    }

    /**
     * @param $name
     * @param $arguments
     */
    public function __call($name, $arguments)
    {
        $this->forwardCallTo($this->route, $name, $arguments);
    }

    /**
     * @return void
     */
    public function __destruct()
    {
        if (false === $this->named) {
            $this->route->name($this->controllerMethod);
        }
    }

    /**
     * @param string $name
     * @return $this
     */
    public function name(string $name): self
    {
        $this->route->name($name);
        $this->named = true;

        return $this;
    }

}
