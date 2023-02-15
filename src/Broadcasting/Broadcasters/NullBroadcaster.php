<?php

namespace Volcano\Broadcasting\Broadcasters;

use Volcano\Broadcasting\Broadcaster;

use Volcano\Http\Request;


class NullBroadcaster extends Broadcaster
{

    /**
     * {@inheritdoc}
     */
    public function authenticate(Request $request)
    {
        //
    }

    /**
     * {@inheritdoc}
     */
    public function validAuthenticationResponse(Request $request, $result)
    {
        //
    }

    /**
     * {@inheritdoc}
     */
    public function broadcast(array $channels, $event, array $payload = array())
    {
        //
    }
}
