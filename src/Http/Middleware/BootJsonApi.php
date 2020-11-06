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

namespace LaravelJsonApi\Http\Middleware;

use Closure;
use Illuminate\Contracts\Container\Container as IlluminateContainer;
use Illuminate\Http\Request;
use Illuminate\Pagination\AbstractPaginator;
use LaravelJsonApi\Contracts\Http\Repository;
use LaravelJsonApi\Contracts\Http\Server;

class BootJsonApi
{

    /**
     * @var IlluminateContainer
     */
    private IlluminateContainer $container;

    /**
     * @var Repository
     */
    private Repository $servers;

    /**
     * BootJsonApi constructor.
     *
     * @param IlluminateContainer $container
     * @param Repository $servers
     */
    public function __construct(IlluminateContainer $container, Repository $servers)
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
        $this->container->instance(
            Server::class,
            $server = $this->servers->server($name)
        );

        $this->bindPageResolver();

        $server->serving();

        return $next($request);
    }

    /**
     * Override the page resolver to read the page parameter from the JSON API request.
     *
     * @return void
     */
    protected function bindPageResolver(): void
    {
        /** Override the current page resolution */
        AbstractPaginator::currentPageResolver(static function ($pageName) {
            $pagination = \request()->query($pageName);

            return $pagination['number'] ?? null;
        });
    }
}
