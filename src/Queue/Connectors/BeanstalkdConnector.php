<?php

namespace Volcano\Queue\Connectors;

use Volcano\Queue\Connectors\ConnectorInterface;
use Volcano\Queue\Queues\BeanstalkdQueue;

use Pheanstalk_Pheanstalk as Pheanstalk;
use Pheanstalk_PheanstalkInterface as PheanstalkInterface;


class BeanstalkdConnector implements ConnectorInterface
{

    /**
     * Establish a queue connection.
     *
     * @param  array  $config
     * @return \Volcano\Queue\Contracts\QueueInterface
     */
    public function connect(array $config)
    {
        $pheanstalk = new Pheanstalk($config['host'], array_get($config, 'port', PheanstalkInterface::DEFAULT_PORT));

        return new BeanstalkdQueue(
            $pheanstalk, $config['queue'], array_get($config, 'ttr', Pheanstalk::DEFAULT_TTR)
        );
    }

}
