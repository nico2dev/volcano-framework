<?php

namespace Volcano\Foundation\Events;


trait DispatchableTrait
{

    /**
     * Dispatch the event with the given arguments.
     *
     * @return void
     */
    public static function dispatch()
    {
        return event(new static(...func_get_args()));
    }
    
    /**
     * Broadcast the event with the given arguments.
     *
     * @return \Volcano\Broadcasting\PendingBroadcast
     */
    public static function broadcast()
    {
        return broadcast(new static(...func_get_args()));
    }
}
