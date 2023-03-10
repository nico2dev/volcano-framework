<?php

namespace Volcano\Support\Facades;

/**
 * @see \Volcano\Routing\Router
 */
class Route extends Facade
{
    
    /**
     * Obtenez le nom enregistrĂ© du composant.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'router'; }

}
