<?php

namespace Volcano\Queue\Queues;

//use Volcano\Database\Query\Expression;
use Volcano\Database\Connection;
use Volcano\Queue\Jobs\DatabaseJob;
use Volcano\Queue\Queue;
use Volcano\Queue\QueueInterface;

use Carbon\Carbon;

use DateTime;


class DatabaseQueue extends Queue implements QueueInterface
{
    /**
     * The database connection instance.
     *
     * @var \Volcano\Database\Connection
     */
    protected $database;

    /**
     * The database table that holds the jobs.
     *
     * @var string
     */
    protected $table;

    /**
     * The name of the default queue.
     *
     * @var string
     */
    protected $default;

    /**
     * The expiration time of a job.
     *
     * @var int|null
     */
    protected $retryAfter = 60;

    /**
     * 
     */
    protected $expire;

    /**
     * Create a new database queue instance.
     *
     * @param  \Volcano\Database\Connection  $database
     * @param  string  $table
     * @param  string  $default
     * @param  int  $retryAfter
     * @return void
     */
    public function __construct(Connection $database, $table, $default = 'default', $retryAfter = 60)
    {
        $this->table = $table;
        $this->default = $default;
        $this->database = $database;
        $this->retryAfter = $retryAfter;
    }

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
        return $this->pushToDatabase(0, $queue, $this->createPayload($job, $data));
    }

    /**
     * Push a raw payload onto the queue.
     *
     * @param  string  $payload
     * @param  string  $queue
     * @param  array   $options
     * @return mixed
     */
    public function pushRaw($payload, $queue = null, array $options = array())
    {
        return $this->pushToDatabase(0, $queue, $payload);
    }

    /**
     * Push a new job onto the queue after a delay.
     *
     * @param  \DateTime|int  $delay
     * @param  string  $job
     * @param  mixed   $data
     * @param  string  $queue
     * @return void
     */
    public function later($delay, $job, $data = '', $queue = null)
    {
        return $this->pushToDatabase($delay, $queue, $this->createPayload($job, $data));
    }

    /**
     * Push an array of jobs onto the queue.
     *
     * @param  array   $jobs
     * @param  mixed   $data
     * @param  string  $queue
     * @return mixed
     */
    public function bulk($jobs, $data = '', $queue = null)
    {
        $queue = $this->getQueue($queue);

        $availableAt = $this->getAvailableAt(0);

        $records = array_map(function ($job) use ($queue, $data, $availableAt)
        {
            return $this->buildDatabaseRecord(
                $queue, $this->createPayload($job, $data), $availableAt
            );

        }, (array) $jobs);

        return $this->database->table($this->table)->insert($records);
    }

    /**
     * Release a reserved job back onto the queue.
     *
     * @param  string  $queue
     * @param  \StdClass  $job
     * @param  int  $delay
     * @return mixed
     */
    public function release($queue, $job, $delay)
    {
        return $this->pushToDatabase($delay, $queue, $job->payload, $job->attempts);
    }

    /**
     * Push a raw payload to the database with a given delay.
     *
     * @param  \DateTime|int  $delay
     * @param  string|null  $queue
     * @param  string  $payload
     * @param  int  $attempts
     * @return mixed
     */
    protected function pushToDatabase($delay, $queue, $payload, $attempts = 0)
    {
        $attributes = $this->buildDatabaseRecord(
            $this->getQueue($queue), $payload, $this->getAvailableAt($delay), $attempts
        );

        return $this->database->table($this->table)->insertGetId($attributes);
    }

    /**
     * Pop the next job off of the queue.
     *
     * @param  string  $queue
     * @return \Volcano\Contracts\Queue\Job|null
     */
    public function pop($queue = null)
    {
        $queue = $this->getQueue($queue);

        return $this->database->transaction(function () use ($queue)
        {
            if (! is_null($job = $this->getNextAvailableJob($queue))) {
                $this->markJobAsReserved($job->id, $job->attempts);

                return new DatabaseJob(
                    $this->container, $this, $job, $queue
                );
            }
        });
    }

    /**
     * Get the next available job for the queue.
     *
     * @param  string|null  $queue
     * @return \StdClass|null
     */
    protected function getNextAvailableJob($queue)
    {
        $job = $this->database->table($this->table)
            ->lockForUpdate()
            ->where('queue', $this->getQueue($queue))
            ->where(function ($query)
            {
                $this->isAvailable($query);

                $this->isReservedButExpired($query);
            })
            ->orderBy('id', 'asc')
            ->first();

        if (! is_null($job)) {
            return (object) $job;
        }
    }

   /**
     * Modify the query to check for available jobs.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return void
     */
    protected function isAvailable($query)
    {
        $query->where(function ($query)
        {
            $query->whereNull('reserved_at')->where('available_at', '<=', $this->getTime());
        });
    }

    /**
     * Modify the query to check for jobs that are reserved but have expired.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return void
     */
    protected function isReservedButExpired($query)
    {
        $expiration = Carbon::now()->subSeconds($this->retryAfter)->getTimestamp();

        $query->orWhere(function ($query) use ($expiration)
        {
            $query->where('reserved_at', '<=', $expiration);
        });
    }

    /**
     * Mark the given job ID as reserved.
     *
     * @param  string  $id
     * @param  int     $attempts
     * @return void
     */
    protected function markJobAsReserved($id, $attempts)
    {
        $this->database->table($this->table)->where('id', $id)->update(array(
            'attempts'    => $attempts,
            'reserved_at' => $this->getTime(),
        ));
    }

    /**
     * Delete a reserved job from the queue.
     *
     * @param  string  $queue
     * @param  string  $id
     * @return void
     */
    public function deleteReserved($queue, $id)
    {
        $this->database->transaction(function () use ($id)
        {
            $record = $this->database->table($this->table)->lockForUpdate()->find($id);

            if (! is_null($record)) {
                $this->database->table($this->table)->where('id', $id)->delete();
            }
        });
    }

    /**
     * Get the "available at" UNIX timestamp.
     *
     * @param  \DateTime|int  $delay
     * @return int
     */
    protected function getAvailableAt($delay)
    {
        $availableAt = ($delay instanceof DateTime) ? $delay : Carbon::now()->addSeconds($delay);

        return $availableAt->getTimestamp();
    }

    /**
     * Create an array to insert for the given job.
     *
     * @param  string|null  $queue
     * @param  string  $payload
     * @param  int  $availableAt
     * @param  int  $attempts
     * @return array
     */
    protected function buildDatabaseRecord($queue, $payload, $availableAt, $attempts = 0)
    {
        return array(
            'queue'        => $queue,
            'payload'      => $payload,
            'attempts'     => $attempts,
            'reserved_at'  => null,
            'available_at' => $availableAt,
            'created_at'   => $this->getTime(),
        );
    }

    /**
     * Get the queue or return the default.
     *
     * @param  string|null  $queue
     * @return string
     */
    protected function getQueue($queue)
    {
        return $queue ?: $this->default;
    }

    /**
     * Get the underlying database instance.
     *
     * @return \Volcano\Database\Connection
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * Get the expiration time in seconds.
     *
     * @return int|null
     */
    public function getExpire()
    {
        return $this->expire;
    }

    /**
     * Set the expiration time in seconds.
     *
     * @param  int|null  $seconds
     * @return void
     */
    public function setExpire($seconds)
    {
        $this->expire = $seconds;
    }

}
