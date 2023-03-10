<?php

namespace Volcano\Broadcasting;

use Volcano\Events\Dispatcher;


class PendingBroadcast
{
    /**
     * The event dispatcher implementation.
     *
     * @var \Volcano\Events\Dispatcher
     */
    protected $events;

    /**
     * The event instance.
     *
     * @var mixed
     */
    protected $event;


    /**
     * Create a new pending broadcast instance.
     *
     * @param  \Volcano\Events\Dispatcher  $events
     * @param  mixed  $event
     * @return void
     */
    public function __construct(Dispatcher $events, $event)
    {
        $this->event  = $event;
        $this->events = $events;
    }

    /**
     * Handle the object's destruction.
     *
     * @return void
     */
    public function __destruct()
    {
        $this->events->dispatch($this->event);
    }

    /**
     * Broadcast the event to everyone except the current user.
     *
     * @return $this
     */
    public function toOthers()
    {
        if (method_exists($this->event, 'dontBroadcastToCurrentUser')) {
            $this->event->dontBroadcastToCurrentUser();
        }

        return $this;
    }

}
