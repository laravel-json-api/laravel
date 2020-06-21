<?php

declare(strict_types=1);

namespace LaravelJsonApi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Pagination\AbstractPaginator;
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

        $this->bindPageResolver();

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
        AbstractPaginator::currentPageResolver(function ($pageName) {
            $pagination = \request()->query($pageName);

            return $pagination['number'] ?? null;
        });
    }
}
