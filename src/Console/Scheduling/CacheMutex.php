<?php

namespace Volcano\Console\Scheduling;

use Volcano\Cache\Repository as Cache;
use Volcano\Console\Scheduling\MutexInterface;


class CacheMutex implements MutexInterface
{
    /**
     * The cache repository implementation.
     *
     * @var \Volcano\Cache\Repository
     */
    public $cache;


    /**
     * Create a new overlapping strategy.
     *
     * @param  \Volcano\Cache\Repository  $cache
     * @return void
     */
    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Attempt to obtain a mutex for the given event.
     *
     * @param  \Volcano\Console\Scheduling\Event  $event
     * @return bool
     */
    public function create(Event $event)
    {
        return $this->cache->add(
            $event->mutexName(), true, $event->expiresAt
        );
    }

    /**
     * Determine if a mutex exists for the given event.
     *
     * @param  \Volcano\Console\Scheduling\Event  $event
     * @return bool
     */
    public function exists(Event $event)
    {
        return $this->cache->has($event->mutexName());
    }

    /**
     * Clear the mutex for the given event.
     *
     * @param  \Volcano\Console\Scheduling\Event  $event
     * @return void
     */
    public function forget(Event $event)
    {
        $this->cache->forget($event->mutexName());
    }
}
