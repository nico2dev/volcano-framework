<?php

namespace Volcano\Support\Facades;

/**
 * @see \Volcano\Routing\Router
 */
class Route extends Facade
{
    
    /**
     * Obtenez le nom enregistré du composant.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'router'; }

}
