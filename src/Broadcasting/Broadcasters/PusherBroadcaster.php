<?php

namespace Volcano\Broadcasting\Broadcasters;

use Volcano\Broadcasting\Broadcaster;
use Volcano\Broadcasting\BroadcastException;

use Volcano\Container\Container;

use Volcano\Http\Request;

use Volcano\Support\Arr;
use Volcano\Support\Str;

use Pusher\Pusher;


class PusherBroadcaster extends Broadcaster
{
    /**
     * The Pusher SDK instance.
     *
     * @var \Pusher
     */
    protected $pusher;


    /**
     * Create a new broadcaster instance.
     *
     * @param  \Volcano\Container\Container  $container
     * @param  \Pusher  $pusher
     * @return void
     */
    public function __construct(Container $container, Pusher $pusher)
    {
        parent::__construct($container);

        //
        $this->pusher = $pusher;
    }

    /**
     * Return the valid authentication response.
     *
     * @param  \Volcano\Http\Request  $request
     * @param  mixed  $result
     * @return mixed
     */
    public function validAuthenticationResponse(Request $request, $result)
    {
        $channel = $request->input('channel_name');

        $socketId = $request->input('socket_id');

        if (! Str::startsWith($channel, 'presence-')) {
            $result = $this->pusher->socket_auth($channel, $socketId);
        } else {
            $user = $request->user();

            $result = $this->pusher->presence_auth(
                $channel, $socketId, $user->getAuthIdentifier(), $result
            );
        }

        return json_decode($result, true);
    }

    /**
     * {@inheritdoc}
     */
    public function broadcast(array $channels, $event, array $payload = array())
    {
        $socket = Arr::pull($payload, 'socket');

        $event = str_replace('\\', '.', $event);

        $response = $this->pusher->trigger(
            $this->formatChannels($channels), $event, $payload, $socket, true
        );

        if (($response['status'] >= 200) && ($response['status'] <= 299)) {
            return;
        }

        throw new BroadcastException($response['body']);
    }

    /**
     * Get the Pusher SDK instance.
     *
     * @return \Pusher
     */
    public function getPusher()
    {
        return $this->pusher;
    }
}
