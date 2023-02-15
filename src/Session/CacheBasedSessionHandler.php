<?php

namespace Volcano\Session;

use Volcano\Cache\Repository;


class CacheBasedSessionHandler implements \SessionHandlerInterface
{
    /**
     * The cache repository instance.
     *
     * @var \Volcano\Cache\Repository
     */
    protected $cache;

    /**
     * The number of minutes to store the data in the cache.
     *
     * @var int
     */
    protected $minutes;

    /**
     * Create a new cache driven handler instance.
     *
     * @param  \Volcano\Cache\Repository  $cache
     * @param  int  $minutes
     * @return void
     */
    public function __construct(Repository $cache, $minutes)
    {
        $this->cache = $cache;
        $this->minutes = $minutes;
    }

    /**
     * {@inheritDoc}
     */
    public function open($savePath, $sessionName): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function read($sessionId): string|false
    {
        return $this->cache->get($sessionId, '');
    }

    /**
     * {@inheritDoc}
     */
    public function write($sessionId, $data)
    {
        return $this->cache->put($sessionId, $data, $this->minutes);
    }

    /**
     * {@inheritDoc}
     */
    public function destroy($sessionId): bool
    {
        return $this->cache->forget($sessionId);
    }

    /**
     * {@inheritDoc}
     */
    public function gc($lifetime): bool
    {
        return true;
    }

    /**
     * Get the underlying cache repository.
     *
     * @return \Volcano\Cache\Repository
     */
    public function getCache()
    {
        return $this->cache;
    }

}
