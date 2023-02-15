<?php

namespace Volcano\Foundation\Bus;

use Volcano\Foundation\Bus\PendingDispatch;


trait DispatchableTrait
{

    /**
     * Dispatch the job with the given arguments.
     *
     * @return \Volcano\Foundation\Bus\PendingDispatch
     */
    public static function dispatch()
    {
        $args = func_get_args();

        return new PendingDispatch(new static(...$args));
    }
}
