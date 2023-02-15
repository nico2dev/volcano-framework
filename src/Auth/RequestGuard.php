<?php

namespace Volcano\Auth;

use Volcano\Auth\GuardHelpersTrait;
use Volcano\Auth\GuardInterface;
use Volcano\Http\Request;


class RequestGuard implements GuardInterface
{
    use GuardHelpersTrait;

    /**
     * The guard callback.
     *
     * @var callable
     */
    protected $callback;

    /**
     * The request instance.
     *
     * @var \Volcano\Http\Request
     */
    protected $request;


    /**
     * Create a new authentication guard.
     *
     * @param  callable  $callback
     * @param  \Volcano\Http\Request  $request
     * @return void
     */
    public function __construct(callable $callback, Request $request)
    {
        $this->request  = $request;
        $this->callback = $callback;
    }

    /**
     * Get the currently authenticated user.
     *
     * @return \Volcano\Auth\UserInterface|null
     */
    public function user()
    {
        // If we've already retrieved the user for the current request we can just
        // return it back immediately. We do not want to fetch the user data on
        // every call to this method because that would be tremendously slow.
        if (! is_null($this->user)) {
            return $this->user;
        }

        return $this->user = call_user_func($this->callback, $this->request);
    }

    /**
     * Validate a user's credentials.
     *
     * @param  array  $credentials
     * @return bool
     */
    public function validate(array $credentials = array())
    {
        $guard = new static($this->callback, $credentials['request']);

        return ! is_null($guard->user());
    }

    /**
     * Set the current request instance.
     *
     * @param  \Volcano\Http\Request  $request
     * @return $this
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }
}
