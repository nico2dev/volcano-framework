<?php

namespace Volcano\Queue\Connectors;

use Volcano\Queue\Connectors\ConnectorInterface;
use Volcano\Queue\Queues\SyncQueue;


class SyncConnector implements ConnectorInterface
{

    /**
     * Establish a queue connection.
     *
     * @param  array  $config
     * @return \Volcano\Queue\Contracts\QueueInterface
     */
    public function connect(array $config)
    {
        return new SyncQueue;
    }

}
