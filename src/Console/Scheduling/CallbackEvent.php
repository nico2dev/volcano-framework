<?php

namespace Volcano\Console\Scheduling;

use Volcano\Container\Container;
use Volcano\Console\Scheduling\MutexInterface as Mutex;

use InvalidArgumentException;
use LogicException;


class CallbackEvent extends Event
{
    /**
     * The callback to call.
     *
     * @var string
     */
    protected $callback;

    /**
     * The parameters to pass to the method.
     *
     * @var array
     */
    protected $parameters;


    /**
     * Create a new event instance.
     *
     * @param  \Volcano\Console\Scheduling\MutexInterface  $mutex
     * @param  string  $callback
     * @param  array  $parameters
     * @return void
     */
    public function __construct(Mutex $mutex, $callback, array $parameters = array())
    {
        if (! is_string($this->callback) && ! is_callable($this->callback)) {
            throw new InvalidArgumentException(
                "Invalid scheduled callback event. Must be string or callable."
            );
        }

        $this->mutex = $mutex;
        $this->callback = $callback;
        $this->parameters = $parameters;
    }

    /**
     * Run the given event.
     *
     * @param  \Volcano\Container\Container  $container
     * @return mixed
     *
     * @throws \Exception
     */
    public function run(Container $container)
    {
        if ($this->description && $this->withoutOverlapping && ! $this->mutex->create($this)) {
            return;
        }

        register_shutdown_function(function ()
        {
            $this->removeMutex();
        });

        parent::callBeforeCallbacks($container);

        try {
            $response = $container->call($this->callback, $this->parameters);
        }
        finally {
            $this->removeMutex();

            parent::callAfterCallbacks($container);
        }

        return $response;
    }

    /**
     * Remove the mutex file from disk.
     *
     * @return void
     */
    protected function removeMutex()
    {
        if (isset($this->description)) {
            $this->mutex->forget($this);
        }
    }

    /**
     * Do not allow the event to overlap each other.
     *
     * @param  int  $expiresAt
     * @return $this
     */
    public function withoutOverlapping($expiresAt = 1440)
    {
        if (! isset($this->description)) {
            throw new LogicException(
                "A scheduled event name is required to prevent overlapping. Use the 'name' method before 'withoutOverlapping'."
            );
        }

        $this->withoutOverlapping = true;

        $this->expiresAt = $expiresAt;

        return $this->skip(function ()
        {
            return $this->mutex->exists($this);
        });
    }

    /**
     * Get the mutex path for the scheduled command.
     *
     * @return string
     */
    public function mutexName()
    {
        return storage_path('schedule-' .sha1($this->description));
    }

    /**
     * Get the summary of the event for display.
     *
     * @return string
     */
    public function getSummaryForDisplay()
    {
        if (is_string($this->description)) {
            return $this->description;
        }

        return is_string($this->callback) ? $this->callback : 'Closure';
    }
}
