<?php
/**
 * Class to find routing between two points.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Map;

/**
 * Base Connector to get routing.
 */
class Routing
{
	/** @var \App\Map\Routing\Base Routing instance */
	private static $instance;

	/**
	 * Function to get connector.
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return \App\Map\Routing\Base
	 */
	public static function getInstance(): Routing\Base
	{
		if (static::$instance) {
			return static::$instance;
		}
		$routingProvider = \App\Config::module('OpenStreetMap', 'routingServers')[\App\Config::module('OpenStreetMap', 'routingServer')];
		$className = "\\App\\Map\\Routing\\{$routingProvider['driverName']}";
		if (!class_exists($className)) {
			throw new \App\Exceptions\AppException('ERR_CLASS_NOT_FOUND');
		}
		static::$instance = new $className($routingProvider);
		return static::$instance;
	}

	/**
	 * Get routing drivers.
	 *
	 * @return string[]
	 */
	public static function getDrivers(): array
	{
		$drivers = [];
		foreach (new \DirectoryIterator(ROOT_DIRECTORY . '/app/Map/Routing/') as $item) {
			if ($item->isFile() && 'Base' !== $item->getBasename('.php')) {
				$drivers[] = $item->getBasename('.php');
			}
		}
		return $drivers;
	}
}
