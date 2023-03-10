<?php

namespace Volcano\Support\Facades;

/**
 * @see \Volcano\Filesystem\Filesystem
 */
class File extends Facade
{
    
    /**
     * Obtenez le nom enregistrĂ© du composant.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'files'; }

}
