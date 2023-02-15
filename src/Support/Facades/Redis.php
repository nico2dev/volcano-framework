<?php

namespace Volcano\Support\Facades;


/**
 * @see \Volcano\Redis\Database
 */
class Redis extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'redis'; }

}
