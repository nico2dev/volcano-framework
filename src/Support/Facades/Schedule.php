<?php

namespace Volcano\Support\Facades;

/**
 * @see \Volcano\Console\Scheduling\Schedule
 */
class Schedule extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'schedule'; }
}
