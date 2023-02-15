<?php

namespace Volcano\Auth\Middleware;

use Volcano\Auth\AuthManager as Auth;
use Volcano\Auth\AuthenticationException;

use Closure;


class Authenticate
{
    /**
     * The authentication factory instance.
     *
     * @var \Volcano\Auth\AuthManager
     */
    protected $auth;


    /**
     * Create a new middleware instance.
     *
     * @param  \Volcano\Auth\AuthManager  $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
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
        $guards = array_slice(func_get_args(), 2);

        $this->authenticate($guards);

        return $next($request);
    }

    /**
     * Determine if the user is logged in to any of the given guards.
     *
     * @param  array  $guards
     * @return void
     *
     * @throws \Volcano\Auth\AuthenticationException
     */
    protected function authenticate(array $guards)
    {
        if (empty($guards)) {
            return $this->auth->authenticate();
        }

        foreach ($guards as $guard) {
            $auth = $this->auth->guard($guard);

            if ($auth->check()) {
                return $this->auth->shouldUse($guard);
            }
        }

        throw new AuthenticationException('Unauthenticated.', $guards);
    }
}
