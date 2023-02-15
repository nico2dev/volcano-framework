<?php

namespace Volcano\Broadcasting\Channels;

use Volcano\Broadcasting\Channel as BaseChannel;


class PublicChannel extends BaseChannel
{
    /**
     * Create a new channel instance.
     *
     * @param  string  $name
     * @return void
     */
    public function __construct($name)
    {
        $this->name = $name;
    }
}
