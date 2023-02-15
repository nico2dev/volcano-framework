<?php

namespace Volcano\Contracts;


interface MessageProviderInterface
{
    
    /**
     * Obtenez les messages pour l'instance.
     *
     * @return \Volcano\Support\MessageBag
     */
    public function getMessageBag();
}
