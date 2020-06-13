<?php

declare(strict_types=1);

namespace LaravelJsonApi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use LaravelJsonApi\Http\Server;

class BootJsonApi
{

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
        app()->instance(Server::class, new Server($name));

        return $next($request);
    }
}
