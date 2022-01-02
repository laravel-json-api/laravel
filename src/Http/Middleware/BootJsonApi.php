<?php
/*
 * Copyright 2021 Cloud Creativity Limited
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

namespace LaravelJsonApi\Laravel\Http\Middleware;

use Closure;
use Illuminate\Container\Container;
use Illuminate\Http\Request;
use LaravelJsonApi\Contracts\Routing\Route as RouteContract;
use LaravelJsonApi\Contracts\Server\Repository;
use LaravelJsonApi\Contracts\Server\Server;
use LaravelJsonApi\Eloquent\Pagination\PagePagination;
use LaravelJsonApi\Laravel\Routing\Route;

class BootJsonApi
{

    /**
     * @var Container
     */
    private Container $container;

    /**
     * @var Repository
     */
    private Repository $servers;

    /**
     * BootJsonApi constructor.
     *
     * @param Container $container
     * @param Repository $servers
     */
    public function __construct(Container $container, Repository $servers)
    {
        $this->container = $container;
        $this->servers = $servers;
    }

    /**
     * Handle the request.
     *
     * @param Request $request
     * @param Closure $next
     * @param string $name
     * @return mixed
     */
    public function handle($request, Closure $next, string $name)
    {
        /**
         * When handling an HTTP request, both the JSON:API server and
         * request classes can be singletons bound into the container.
         * (The middleware will remove these instances from the container
         * once the HTTP request is terminated.)
         */
        $this->container->instance(
            Server::class,
            $server = $this->servers->server($name)
        );

        $this->container->instance(
            RouteContract::class,
            $route = new Route($this->container, $server, $request->route())
        );

        /**
         * Before we do anything, we must ensure the server is set up to
         * handle an HTTP request. We do that by invoking the `serving()`
         * hook on the server instance.
         */
        if (method_exists($server, 'serving')) {
            $this->container->call([$server, 'serving']);
        }

        /**
         * Once the server is set up, we can substitute bindings. This must
         * happen after the `serving` hook, in case that hook has added any
         * Eloquent scopes.
         */
        $route->substituteBindings();

        /**
         * We will also override the Laravel page resolver, as we know this is
         * a JSON:API request, and the specification would have the page number
         * nested under the `page` query parameter.
         */
        PagePagination::bindPageResolver();

        return $next($request);
    }

    /**
     * Handle tasks after the response has been sent.
     *
     * @return void
     */
    public function terminate(): void
    {
        $this->container->forgetInstance(Server::class);
        $this->container->forgetInstance(RouteContract::class);
    }
}
