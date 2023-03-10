<?php

namespace Volcano\Support\Facades;

use Volcano\Support\Facades\Facade;


/**
 * @see \Volcano\Config\Repository
 */
class Config extends Facade
{
    
    /**
     * Obtenez le nom enregistrĂ© du composant.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'config'; }

}
