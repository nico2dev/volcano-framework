<?php

namespace Volcano\Notifications\Events;

use Volcano\Bus\QueueableTrait;
use Volcano\Queue\SerializesModelsTrait;

use Volcano\Broadcasting\Contracts\ShouldBroadcastInterface;


class BroadcastNotificationCreated implements ShouldBroadcastInterface
{
    use QueueableTrait, SerializesModelsTrait;

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
     * The notification data.
     *
     * @var array
     */
    public $data = array();

    /**
     * Create a new event instance.
     *
     * @param  mixed  $notifiable
     * @param  \Volcano\Notifications\Notification  $notification
     * @param  array  $data
     * @return void
     */
    public function __construct($notifiable, $notification, $data)
    {
        $this->data = $data;
        $this->notifiable = $notifiable;
        $this->notification = $notification;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        $channels = $this->notification->broadcastOn();

        if (! empty($channels)) {
            return $channels;
        }

        $channel = 'private-' .$this->channelName();

        return array($channel);
    }

    /**
     * Get the data that should be sent with the broadcasted event.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return array_merge($this->data, array(
            'id'   => $this->notification->id,
            'type' => get_class($this->notification),
        ));
    }

    /**
     * Get the broadcast channel name for the event.
     *
     * @return string
     */
    protected function channelName()
    {
        if (method_exists($this->notifiable, 'receivesBroadcastNotificationsOn')) {
            return $this->notifiable->receivesBroadcastNotificationsOn($this->notification);
        }

        $className = str_replace('\\', '.', get_class($this->notifiable));

        return $className .'.' .$this->notifiable->getKey();
    }
}
