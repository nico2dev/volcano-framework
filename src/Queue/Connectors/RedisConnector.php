<?php

namespace Volcano\Queue\Connectors;

use Volcano\Queue\Connectors\ConnectorInterface;
use Volcano\Queue\Queues\RedisQueue;
use Volcano\Redis\Database;


class RedisConnector implements ConnectorInterface
{

    /**
    * The Redis database instance.
    *
     * @var \Volcano\Redis\Database
     */
    protected $redis;

    /**
     * The connection name.
     *
     * @var string
     */
    protected $connection;

    /**
     * Create a new Redis queue connector instance.
     *
     * @param  \Volcano\Redis\Database  $redis
     * @param  string|null  $connection
     * @return void
     */
    public function __construct(Database $redis, $connection = null)
    {
        $this->redis = $redis;
        $this->connection = $connection;
    }

    /**
     * Establish a queue connection.
     *
     * @param  array  $config
     * @return \Volcano\Queue\Contracts\QueueInterface
     */
    public function connect(array $config)
    {
        $queue = new RedisQueue(
            $this->redis, $config['queue'], array_get($config, 'connection', $this->connection)
        );

        $queue->setExpire(array_get($config, 'expire', 60));

        return $queue;
    }

}
