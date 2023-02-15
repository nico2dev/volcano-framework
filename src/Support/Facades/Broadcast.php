<?php

namespace Volcano\Support\Facades;

use Volcano\Broadcasting\FactoryInterface as BroadcastingFactory;


/**
 * @see \Volcano\Broadcasting\FactoryInterface
 */
class Broadcast extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return BroadcastingFactory::class;
    }
}
