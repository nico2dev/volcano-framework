<?php

namespace Volcano\Broadcasting;

use Volcano\Http\Request;


interface BroadcasterInterface
{
    /**
     * Authenticate the incoming request for a given channel.
     *
     * @param  \Volcano\Http\Request  $request
     * @return mixed
     */
    public function authenticate(Request $request);

    /**
     * Return the valid authentication response.
     *
     * @param  \Volcano\Http\Request  $request
     * @param  mixed  $result
     * @return mixed
     */
    public function validAuthenticationResponse(Request $request, $result);

    /**
     * Broadcast the given event.
     *
     * @param  array  $channels
     * @param  string  $event
     * @param  array  $payload
     * @return void
     */
    public function broadcast(array $channels, $event, array $payload = array());
}
