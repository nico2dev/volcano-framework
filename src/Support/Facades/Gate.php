<?php

namespace Volcano\Support\Facades;

/**
 * @see \Volcano\Auth\Access\GateInterface
 */
class Gate extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'Volcano\Auth\Access\GateInterface';
    }
}
