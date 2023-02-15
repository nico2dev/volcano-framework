<?php

namespace Volcano\Support\Facades;

use Volcano\Support\Facades\Facade;


/**
 * @see \Volcano\Foundation\Application
 */
class App extends Facade
{
    
    /**
     * Renvoie l'instance Application.
     *
     * @return \Volcano\Foundation\Application
     */
    public static function instance()
    {
        $accessor = static::getFacadeAccessor();

        return static::resolveFacadeInstance($accessor);
    }

    /**
     * Obtenez le nom enregistré du composant.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'app'; }
}
