<?php
/**
 * Class to get route.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */

namespace App\Map;

/**
 * Base Connector to get route.
 */
class Route
{
	/**
	 * Function to get connector.
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return \App\Map\Route\Base
	 */
	public static function getInstance()
	{
		$type = \AppConfig::module('OpenStreetMap', 'ROUTE_CONNECTOR');
		$className = "\App\Map\Route\\$type";
		if (!class_exists($className)) {
			throw new \App\Exceptions\AppException('ERR_CLASS_NOT_FOUND');
		}
		return new $className();
	}
}
