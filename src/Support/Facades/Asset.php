<?php

namespace Volcano\Support\Facades;

use Volcano\Support\Facades\Facade;


/**
 * @see \Volcano\Assets\AuthManager
 */
class Asset extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'assets'; }
}
