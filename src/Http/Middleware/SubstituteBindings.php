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

namespace LaravelJsonApi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use LaravelJsonApi\Contracts\Routing\Route;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class SubstituteBindings
{

    /**
     * @var Route
     */
    private Route $route;

    /**
     * SubstituteBindings constructor.
     *
     * @param Route $route
     */
    public function __construct(Route $route)
    {
        $this->route = $route;
    }

    /**
     * Handle the request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     * @throws HttpExceptionInterface
     */
    public function handle($request, Closure $next)
    {
        $this->route->substituteBindings();

        return $next($request);
    }
}
