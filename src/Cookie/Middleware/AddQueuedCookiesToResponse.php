<?php

namespace Volcano\Cookie\Middleware;

use Volcano\Foundation\Application;

use Closure;


class AddQueuedCookiesToResponse
{
    /**
     * The cookie jar instance.
     *
     * @var \Volcano\Cookie\CookieJar
     */
    protected $cookies;

    /**
     * Create a new CookieQueue instance.
     *
     * @param  \Volcano\Foundation\Application  $app
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->cookies = $app['cookie'];
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
        $response = $next($request);

        foreach ($this->cookies->getQueuedCookies() as $cookie) {
            $response->headers->setCookie($cookie);
        }

        return $response;
    }
}
