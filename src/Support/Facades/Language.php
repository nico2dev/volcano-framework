<?php

namespace Volcano\Support\Facades;

use Volcano\Support\Facades\Facade;

/**
 * @see \Volcano\Language\Language
 * @see \Volcano\Language\LanguageManager
 */
class Language extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'language'; }

}
