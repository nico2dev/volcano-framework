<?php

namespace Volcano\Routing\Middleware;

use Volcano\Foundation\Application;

//use Symfony\Component\HttpKernel\Exception\HttpException;

use Closure;


class DispatchAssetFiles
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
     */
    public function handle($request, Closure $next)
    {
        $dispatcher = $this->app['assets.dispatcher'];

        return $dispatcher->dispatch($request) ?: $next($request);
    }
}
