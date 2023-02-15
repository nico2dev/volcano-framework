<?php 

namespace Volcano\Support\Facades;

/**
 * @see \Volcano\Queue\QueueManager
 * @see \Volcano\Queue\Queue
 */
class Queue extends Facade
{

    /**
    * Get the registered name of the component.
    *
    * @return string
    */
    protected static function getFacadeAccessor() { return 'queue'; }

}
