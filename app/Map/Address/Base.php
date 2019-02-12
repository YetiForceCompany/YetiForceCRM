<?php

namespace App\Map\Address;

/**
 * Base caching class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
abstract class Base
{
	/**
	 * Function checks if provider is active.
	 *
	 * @return bool
	 */
	public static function isActive()
	{
		return true;
	}

	/**
	 * Find address.
	 *
	 * @param $value string
	 *
	 * @return array
	 */
	abstract public function find($value);
}
