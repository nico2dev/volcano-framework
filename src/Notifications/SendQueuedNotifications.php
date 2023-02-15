<?php

namespace Volcano\Notifications;

use Volcano\Bus\QueueableTrait;
use Volcano\Queue\ShouldQueueInterface;
use Volcano\Queue\SerializesModelsTrait;

use Volcano\Notifications\ChannelManager;


class SendQueuedNotifications implements ShouldQueueInterface
{
    use QueueableTrait, SerializesModelsTrait;

    /**
     * The notifiable entities that should receive the notification.
     *
     * @var \Volcano\Support\Collection
     */
    protected $notifiables;

    /**
     * The notification to be sent.
     *
     * @var \Volcano\Notifications\Notification
     */
    protected $notification;

    /**
     * All of the channels to send the notification too.
     *
     * @var array
     */
    protected $channels;


    /**
     * Create a new job instance.
     *
     * @param  \Volcano\Support\Collection  $notifiables
     * @param  \Volcano\Notifications\Notification  $notification
     * @param  array  $channels
     * @return void
     */
    public function __construct($notifiables, $notification, array $channels = null)
    {
        $this->channels = $channels;
        $this->notifiables = $notifiables;
        $this->notification = $notification;
    }

    /**
     * Send the notifications.
     *
     * @param  \Volcano\Notifications\ChannelManager  $manager
     * @return void
     */
    public function handle(ChannelManager $manager)
    {
        $manager->sendNow($this->notifiables, $this->notification, $this->channels);
    }
}
