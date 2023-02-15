<?php

namespace Volcano\Queue\Connectors;

use Volcano\Database\ConnectionResolverInterface;
use Volcano\Queue\Connectors\ConnectorInterface;
use Volcano\Queue\Queues\DatabaseQueue;
use Volcano\Support\Arr;


class DatabaseConnector implements ConnectorInterface
{
    /**
     * Database connections.
     *
     * @var \Volcano\Database\ConnectionResolverInterface
     */
    protected $connections;

    /**
     * Create a new connector instance.
     *
     * @param  \Volcano\Database\ConnectionResolverInterface  $connections
     * @return void
     */
    public function __construct(ConnectionResolverInterface $connections)
    {
        $this->connections = $connections;
    }

    /**
     * Establish a queue connection.
     *
     * @param  array  $config
     * @return \Volcano\Contracts\Queue\Queue
     */
    public function connect(array $config)
    {
        $connection = Arr::get($config, 'connection');

        return new DatabaseQueue(
            $this->connections->connection($connection),

            $config['table'],
            $config['queue'],

            Arr::get($config, 'expire', 60)
        );
    }
}
