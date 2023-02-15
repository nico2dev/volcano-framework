<?php

namespace Volcano\Notifications;


interface DispatcherInterface
{
    /**
     * Send the given notification to the given notifiable entities.
     *
     * @param  \Volcano\Support\Collection|array|mixed  $notifiables
     * @param  mixed  $notification
     * @return void
     */
    public function send($notifiables, $notification);
}
