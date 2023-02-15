<?php

namespace Volcano\Support\Facades;

use Volcano\Support\Facades\Facade;


/**
 * @see \Volcano\View\Factory
 * @see \Volcano\View\View
 */
class View extends Facade
{

    /**
     * Obtenez le nom enregistré du composant.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'view'; }

}
