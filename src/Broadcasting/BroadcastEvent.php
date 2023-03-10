<?php

namespace Volcano\Broadcasting;

use Volcano\Broadcasting\BroadcasterInterface;

use Volcano\Queue\Job;

use Volcano\Contracts\ArrayableInterface;

use ReflectionClass;
use ReflectionProperty;


class BroadcastEvent
{
    /**
     * The broadcaster implementation.
     *
     * @var \Volcano\Broadcasting\BroadcasterInterface
     */
    protected $broadcaster;


    /**
     * Create a new job handler instance.
     *
     * @param  \Volcano\Broadcasting\BroadcasterInterface  $broadcaster
     * @return void
     */
    public function __construct(BroadcasterInterface $broadcaster)
    {
        $this->broadcaster = $broadcaster;
    }

    /**
     * Handle the queued job.
     *
     * @param  \Volcano\Queue\Job  $job
     * @param  array  $data
     * @return void
     */
    public function handle(Job $job, array $data)
    {
        $event = unserialize($data['event']);

        if (method_exists($event, 'broadcastAs')) {
            $name = $event->broadcastAs();
        } else {
            $name = get_class($event);
        }

        if (! is_array($channels = $event->broadcastOn())) {
            $channels = array($channels);
        }

        $this->broadcaster->broadcast(
            $channels, $name, $this->getPayloadFromEvent($event)
        );

        $job->delete();
    }

    /**
     * Get the payload for the given event.
     *
     * @param  mixed  $event
     * @return array
     */
    protected function getPayloadFromEvent($event)
    {
        if (method_exists($event, 'broadcastWith')) {
            return $event->broadcastWith();
        }

        $payload = array();

        //
        $reflection = new ReflectionClass($event);

        foreach ($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $key = $property->getName();

            $value = $property->getValue($event);

            $payload[$key] = $this->formatProperty($value);
        }

        return $payload;
    }

    /**
     * Format the given value for a property.
     *
     * @param  mixed  $value
     * @return mixed
     */
    protected function formatProperty($value)
    {
        if ($value instanceof ArrayableInterface) {
            return $value->toArray();
        }

        return $value;
    }
}
