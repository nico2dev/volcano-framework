<?php

namespace Volcano\Foundation\Http\Middleware;

use Volcano\Foundation\Application;

//use Volcano\Http\Response;

//use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

use Closure;


class CheckForMaintenanceMode
{
    /**
     * The application implementation.
     *
     * @var \Volcano\Foundation\Application
     */
    protected $app;

    /**
     * Create a new middleware instance.
     *
     * @param  \Volcano\Foundation\Application  $app
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Volcano\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function handle($request, Closure $next)
    {
        if ($this->app->isDownForMaintenance()) {
            throw new HttpException(503);
        }

        return $next($request);
    }
}
