<?php

namespace Volcano\Support\Facades;

use Volcano\Support\Facades\Facade;


/**
 * Class Section
 * @package Volcano\Support\Facades
 */
class Section extends Facade
{
	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor() { return 'view.section'; }

}
