<?php

namespace Volcano\Notifications\Events;


class NotificationSending
{
    /**
     * The notifiable entity who received the notification.
     *
     * @var mixed
     */
    public $notifiable;

    /**
     * The notification instance.
     *
     * @var \Volcano\Notifications\Notification
     */
    public $notification;

    /**
     * The channel name.
     *
     * @var string
     */
    public $channel;

    /**
     * Create a new event instance.
     *
     * @param  mixed  $notifiable
     * @param  \Volcano\Notifications\Notification  $notification
     * @param  string  $channel
     * @return void
     */
    public function __construct($notifiable, $notification, $channel)
    {
        $this->channel = $channel;
        $this->notifiable = $notifiable;
        $this->notification = $notification;
    }
}
