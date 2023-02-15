<?php

namespace Volcano\Queue\Connectors;

use Volcano\Queue\Connectors\ConnectorInterface;
use Volcano\Queue\Queues\NullQueue;


class NullConnector implements ConnectorInterface
{
    /**
     * Establish a queue connection.
     *
     * @param  array  $config
     * @return \Volcano\Contracts\Queue\Queue
     */
    public function connect(array $config)
    {
        return new NullQueue;
    }
}
