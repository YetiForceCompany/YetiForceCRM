<?php
namespace App;

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
		return static::$versions[$type];
	}

	/**
	 * Function to load versions
	 */
	private static function init()
	{
		if (static::$versions === false) {
			static::$versions = require 'config/version.php';
		}
	}

	/**
	 * Check app versions with given version
	 * @param string $version - String Version against which comparision to be done
	 * @param string $type
	 * @param string $condition - String Condition like ( '=', '!=', '<', '<=', '>', '>=')
	 * @return bool|int
	 */
	public static function check($version, $type = 'appVersion', $condition = '>=')
	{
		static::init();
		return version_compare($version, static::$versions[$type], $condition);
	}
}
