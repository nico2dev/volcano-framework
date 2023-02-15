<?php

namespace Volcano\Auth\Access;

interface GateInterface
{
    /**
     * Determine if a given ability has been defined.
     *
     * @param  string  $ability
     * @return bool
     */
    public function has($ability);

    /**
     * Define a new ability.
     *
     * @param  string  $ability
     * @param  callable|string  $callback
     * @return $this
     */
    public function define($ability, $callback);

    /**
     * Define a policy class for a given class type.
     *
     * @param  string  $class
     * @param  string  $policy
     * @return $this
     */
    public function policy($class, $policy);

    /**
     * Determine if the given ability should be granted.
     *
     * @param  string  $ability
     * @param  array|mixed  $arguments
     * @return bool
     */
    public function check($ability, $arguments = []);

    /**
     * Determine if the given ability should be granted for the current user.
     *
     * @param  string  $ability
     * @param  array|mixed  $arguments
     * @return \Volcano\Auth\Access\Response
     *
     * @throws \Volcano\Auth\Access\AuthorizationException
     */
    public function authorize($ability, $arguments = array());    
}
