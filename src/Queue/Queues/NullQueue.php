<?php

namespace Volcano\Queue\Queues;

use Volcano\Queue\QueueInterface;
use Volcano\Queue\Queue;

class NullQueue extends Queue implements QueueInterface
{
    /**
     * Push a new job onto the queue.
     *
     * @param  string  $job
     * @param  mixed   $data
     * @param  string  $queue
     * @return mixed
     */
    public function push($job, $data = '', $queue = null)
    {
        //
    }

    /**
     * Push a raw payload onto the queue.
     *
     * @param  string  $payload
     * @param  string  $queue
     * @param  array   $options
     * @return mixed
     */
    public function pushRaw($payload, $queue = null, array $options = [])
    {
        //
    }

    /**
     * Push a new job onto the queue after a delay.
     *
     * @param  \DateTime|int  $delay
     * @param  string  $job
     * @param  mixed   $data
     * @param  string  $queue
     * @return mixed
     */
    public function later($delay, $job, $data = '', $queue = null)
    {
        //
    }

    /**
     * Pop the next job off of the queue.
     *
     * @param  string  $queue
     * @return \Volcano\Queue\Jobs\Job|null
     */
    public function pop($queue = null)
    {
        //
    }
}
