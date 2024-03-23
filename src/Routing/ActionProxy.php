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
