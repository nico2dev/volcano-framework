<?php

namespace Volcano\Queue\Jobs;

use Volcano\Container\Container;
use Volcano\Queue\Queues\RedisQueue;
use Volcano\Queue\Job;


class RedisJob extends Job
{

    /**
     * The Redis queue instance.
     *
     * @var \Volcano\Queue\RedisQueue
     */
    protected $redis;

    /**
     * The Redis job payload.
     *
     * @var string
     */
    protected $job;

    /**
     * Create a new job instance.
     *
     * @param  \Volcano\Container\Container  $container
     * @param  \Volcano\Queue\RedisQueue  $redis
     * @param  string  $job
     * @param  string  $queue
     * @return void
     */
    public function __construct(Container $container, RedisQueue $redis, $job, $queue)
    {
        $this->job = $job;
        $this->redis = $redis;
        $this->queue = $queue;
        $this->container = $container;
    }

    /**
     * Fire the job.
     *
     * @return void
     */
    public function handle()
    {
        $payload = json_decode($this->getRawBody(), true);

        $this->resolveAndHandle($payload);
    }

    /**
     * Get the raw body string for the job.
     *
     * @return string
     */
    public function getRawBody()
    {
        return $this->job;
    }

    /**
     * Delete the job from the queue.
     *
     * @return void
     */
    public function delete()
    {
        parent::delete();

        $this->redis->deleteReserved($this->queue, $this->job);
    }

    /**
     * Release the job back into the queue.
     *
     * @param  int   $delay
     * @return void
     */
    public function release($delay = 0)
    {
        $this->delete();

        $this->redis->release($this->queue, $this->job, $delay, $this->attempts() + 1);
    }

    /**
     * Get the number of times the job has been attempted.
     *
     * @return int
     */
    public function attempts()
    {
        return array_get(json_decode($this->job, true), 'attempts');
    }

    /**
     * Get the job identifier.
     *
     * @return string
     */
    public function getJobId()
    {
        return array_get(json_decode($this->job, true), 'id');
    }

    /**
     * Get the IoC container instance.
     *
     * @return \Volcano\Container\Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Get the underlying queue driver instance.
     *
     * @return \Volcano\Redis\Database
     */
    public function getRedisQueue()
    {
        return $this->redis;
    }

    /**
     * Get the underlying Redis job.
     *
     * @return string
     */
    public function getRedisJob()
    {
        return $this->job;
    }

}
