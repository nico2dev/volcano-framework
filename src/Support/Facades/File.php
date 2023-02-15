<?php

namespace Volcano\Support\Facades;

/**
 * @see \Volcano\Filesystem\Filesystem
 */
class File extends Facade
{
    
    /**
     * Obtenez le nom enregistré du composant.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'files'; }

}
