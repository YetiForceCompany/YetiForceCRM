<?php namespace App;

/**
 * Version class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Version
{

	private static $versions = false;

	/**
	 * Get current version of system.
	 * @param string $type
	 * @return string
	 */
	public static function get($type = 'appVersion')
	{
		static::init();
		return self::$versions[$type];
	}

	/**
	 * Function to load versions
	 */
	private static function init()
	{
		if (self::$versions === false) {
			self::$versions = require 'config/version.php';
		}
	}
	/**
	 * 
	 * @param String Version against which comparision to be done
	 * @param String Condition like ( '=', '!=', '<', '<=', '>', '>=')
	 */

	/**
	 * Check app versions with given version
	 * @param string $version
	 * @param string $type
	 * @param string $condition
	 * @return bool|int
	 */
	public static function check($version, $type = 'appVersion', $condition = '>=')
	{
		static::init();
		return version_compare($version, self::$versions[$type], $condition);
	}
}
