<?php

namespace Volcano\Queue\Connectors;

use Volcano\Queue\Connectors\ConnectorInterface;
use Volcano\Queue\Queues\SqsQueue;
use Aws\Sqs\SqsClient;


class SqsConnector implements ConnectorInterface
{

    /**
     * Establish a queue connection.
     *
     * @param  array  $config
     * @return \Volcano\Queue\Contracts\QueueInterface
     */
    public function connect(array $config)
    {
        $sqs = SqsClient::factory($config);

        return new SqsQueue($sqs, $config['queue']);
    }

}
