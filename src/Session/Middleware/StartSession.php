<?php

namespace Volcano\Session\Middleware;

use Volcano\Http\Request;
use Volcano\Session\SessionManager;
use Volcano\Session\SessionInterface;
use Volcano\Session\CookieSessionHandler;
use Volcano\Support\Arr;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

use Carbon\Carbon;

use Closure;


class StartSession
{
    /**
     * The session manager.
     *
     * @var \Volcano\Session\SessionManager
     */
    protected $manager;

    /**
     * Indicates if the session was handled for the current request.
     *
     * @var bool
     */
    protected $sessionHandled = false;


    /**
     * Create a new session middleware.
     *
     * @param  \Volcano\Session\SessionManager  $manager
     * @return void
     */
    public function __construct(SessionManager $manager)
    {
        $this->manager = $manager;
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
        $this->sessionHandled = true;

        //
        $sessionConfigured = $this->sessionConfigured();

        if ($sessionConfigured) {
            $session = $this->startSession($request);

            $request->setSession($session);
        }

        $response = $next($request);

        if ($sessionConfigured) {
            $this->storeCurrentUrl($request, $session);

            $this->collectGarbage($session);

            $this->addCookieToResponse($response, $session);
        }

        return $response;
    }

    /**
     * Perform any final actions for the request lifecycle.
     *
     * @param  \Volcano\Http\Request  $request
     * @param  \Symfony\Component\HttpFoundation\Response  $response
     * @return void
     */
    public function terminate($request, $response)
    {
        $sessionConfigured = $this->sessionConfigured();

        if ($this->sessionHandled && $sessionConfigured && ! $this->usingCookieSessions()) {
            $this->manager->driver()->save();
        }
    }

    /**
     * Start the session for the given request.
     *
     * @param  \Volcano\Http\Request  $request
     * @return \Volcano\Session\SessionInterface
     */
    protected function startSession(Request $request)
    {
        $session = $this->manager->driver();

        $session->setId(
            $this->getSessionId($request, $session)
        );

        $session->setRequestOnHandler($request);

        $session->start();

        return $session;
    }

    /**
     * Get the session ID from the request.
     *
     * @param  \Volcano\Http\Request  $request
     * @param  \Volcano\Session\SessionInterface $session
     * @return string|null
     */
    protected function getSessionId(Request $request, SessionInterface $session)
    {
        $name = $session->getName();

        return $request->cookies->get($name);
    }

    /**
     * Store the current URL for the request if necessary.
     *
     * @param  \Volcano\Http\Request  $request
     * @param  \Volcano\Session\SessionInterface  $session
     * @return void
     */
    protected function storeCurrentUrl(Request $request, SessionInterface $session)
    {
        if (($request->method() === 'GET') && $request->route() && ! $request->ajax()) {
            $session->setPreviousUrl($request->fullUrl());
        }
    }

    /**
     * Remove the garbage from the session if necessary.
     *
     * @param  \Volcano\Session\SessionInterface  $session
     * @return void
     */
    protected function collectGarbage(SessionInterface $session)
    {
        $config = $this->getSessionConfig();

        if ($this->configHitsLottery($config)) {
            $lifetime = Arr::get($config, 'lifetime', 180);

            $session->getHandler()->gc($lifetime * 60);
        }
    }

    /**
     * Determine if the configuration odds hit the lottery.
     *
     * @param  array  $config
     * @return bool
     */
    protected function configHitsLottery(array $config)
    {
        list ($trigger, $max) = $config['lottery'];

        $value = mt_rand(1, $max);

        return ($value <= $trigger);
    }

    /**
     * Add the session cookie to the application response.
     *
     * @param  \Symfony\Component\HttpFoundation\Response  $response
     * @param  \Volcano\Session\SessionInterface  $session
     * @return void
     */
    protected function addCookieToResponse(Response $response, SessionInterface $session)
    {
        if ($this->usingCookieSessions()) {
            $this->manager->driver()->save();
        }

        $config = $this->getSessionConfig();

        if ($this->sessionIsPersistent($config)) {
            $cookie = $this->createCookie($config, $session);

            $response->headers->setCookie($cookie);
        }
    }

    /**
     * Create a Cookie instance for the specified Session and configuration.
     *
     * @param  array  $config
     * @param  \Volcano\Session\SessionInterface  $session
     * @return \Symfony\Component\HttpFoundation\Cookie
     */
    protected function createCookie(array $config, SessionInterface $session)
    {
        $expireOnClose = Arr::get($config, 'expireOnClose', false);

        if ($expireOnClose !== false) {
            $lifetime = Arr::get($config, 'lifetime', 180);

            $expire = Carbon::now()->addMinutes($lifetime);
        } else {
            $expire = 0;
        }

        $secure = Arr::get($config, 'secure', false);

        return new Cookie(
            $session->getName(),
            $session->getId(),
            $expire,
            $config['path'],
            $config['domain'],
            $secure
        );
    }

    /**
     * Determine if a session driver has been configured.
     *
     * @return bool
     */
    protected function sessionConfigured()
    {
        $config = $this->getSessionConfig();

        return Arr::has($config, 'driver');
    }

    /**
     * Determine if the configured session driver is persistent.
     *
     * @param  array|null  $config
     * @return bool
     */
    protected function sessionIsPersistent(array $config = null)
    {
        if (is_null($config)) {
            $config = $this->getSessionConfig();
        }

        return ! in_array($config['driver'], array(null, 'array'));
    }

    /**
     * Determine if the session is using cookie sessions.
     *
     * @return bool
     */
    protected function usingCookieSessions()
    {
        if (! $this->sessionConfigured()) {
            return false;
        }

        $session = $this->manager->driver();

        //
        $handler = $session->getHandler();

        return ($handler instanceof CookieSessionHandler);
    }


    /**
     * Returns the Session configuration from manager.
     *
     * @return array
     */
    protected function getSessionConfig()
    {
        return $this->manager->getSessionConfig();
    }
}
