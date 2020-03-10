<?php
/**
 * File layer file.
 *
 * @package App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Map;

/**
 * Map layer class.
 */
class Layer
{
	/**
	 * Get url for tile server.
	 *
	 * @return string
	 */
	public static function getTileServer(): string
	{
		$url = \App\Config::module('OpenStreetMap', 'tileLayerServer');
		if ('YetiForce' === $url) {
			$url = \Config\Main::$site_URL . 'file.php?module=OpenStreetMap&action=TileLayer&z={z}&x={x}&y={y}';
		}
		return $url;
	}
}
