<?php

namespace Volcano\Support\Facades;

use Volcano\Support\Facades\Facade;


/**
 * @see \Volcano\Session\SessionManager
 * @see \Volcano\Session\Store
 */
class Session extends Facade
{
    /**
     * Return the Application instance.
     *
     * @return \Volcano\Pagination\Factory
     */
    public static function instance()
    {
        $accessor = static::getFacadeAccessor();

        return static::resolveFacadeInstance($accessor);
    }

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'session'; }

}
