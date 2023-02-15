<?php

namespace Volcano\Queue\Connectors;

use IronMQ;

use Volcano\Http\Request;
use Volcano\Queue\Connectors\ConnectorInterface;
use Volcano\Queue\Queues\IronQueue;
use Volcano\Encryption\Encrypter;


class IronConnector implements ConnectorInterface
{

    /**
     * The encrypter instance.
     *
     * @var \Volcano\Encryption\Encrypter
     */
    protected $crypt;

    /**
     * The current request instance.
     *
     * @var \Volcano\Http\Request
     */
    protected $request;

    /**
     * Create a new Iron connector instance.
     *
     * @param  \Volcano\Encryption\Encrypter  $crypt
     * @param  \Volcano\Http\Request  $request
     * @return void
     */
    public function __construct(Encrypter $crypt, Request $request)
    {
        $this->crypt = $crypt;
        $this->request = $request;
    }

    /**
     * Establish a queue connection.
     *
     * @param  array  $config
     * @return \Volcano\Queue\Contracts\QueueInterface
     */
    public function connect(array $config)
    {
        $ironConfig = array('token' => $config['token'], 'project_id' => $config['project']);

        if (isset($config['host'])) $ironConfig['host'] = $config['host'];

        $iron = new IronMQ($ironConfig);

        if (isset($config['ssl_verifypeer']))
        {
            $iron->ssl_verifypeer = $config['ssl_verifypeer'];
        }

        return new IronQueue($iron, $this->request, $config['queue'], $config['encrypt']);
    }

}
