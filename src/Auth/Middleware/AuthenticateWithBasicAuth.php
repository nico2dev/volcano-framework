<?php

namespace Volcano\Auth\Middleware;

use Volcano\Support\Facades\Auth;

use Closure;


class AuthenticateWithBasicAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Volcano\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        return Auth::guard($guard)->basic() ?: $next($request);
    }
}
