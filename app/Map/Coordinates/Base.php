<?php
/**
 * Class to get coordinates.
 *
 * @package App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */

namespace App\Map\Coordinates;

/**
 * Base Connector to get coordinates.
 */
abstract class Base
{
	/**
	 * Function to get coordinates from base information about address.
	 *
	 * @param array $addressInfo
	 *
	 * @return string[]|bool
	 */
	abstract public function getCoordinates(array $addressInfo);

	/**
	 * Function to get coordinates from string.
	 *
	 * @param string $value
	 *
	 * @return string[]|bool
	 */
	abstract public function getCoordinatesByValue(string $value);
}
