<?php

namespace Volcano\Queue;


interface QueueableEntityInterface
{
    /**
     * Get the queueable identity for the entity.
     *
     * @return mixed
     */
    public function getQueueableId();
}
