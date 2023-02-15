<?php

namespace Volcano\Support\Facades;

use Volcano\Support\Facades\Facade;


/**
 * @see \Volcano\Cache\CacheManager
 * @see \Volcano\Cache\Repository
 */
class Cache extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'cache'; }

}
