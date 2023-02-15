<?php

namespace Volcano\Support\Facades;

use Volcano\Support\Facades\Facade;


/**
 * @see \Volcano\Routing\ResponseFactory
 */
class Response extends Facade
{
    
    /**
     * Obtenez le nom enregistré du composant.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'response.factory'; }

}
