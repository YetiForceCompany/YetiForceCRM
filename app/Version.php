<?php

namespace App;

/**
 * Version class.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Version
{
	private static $versions = false;

	/**
	 * Get current version of system.
	 *
	 * @param string $type
	 *
	 * @return string
	 */
	public static function get($type = 'appVersion'): string
	{
		static::init();
		return static::$versions[$type];
	}

	/**
	 * Get current short version of system.
	 *
	 * @param string $type
	 *
	 * @return string
	 */
	public static function getShort($type = 'appVersion'): string
	{
		$fullVer = \explode('.', self::get($type));
		array_pop($fullVer);
		return \implode('.', $fullVer);
	}

	/**
	 * Function to load versions.
	 */
	private static function init(): void
	{
		if (false === static::$versions) {
			static::$versions = require 'config/version.php';
		}
	}

	/**
	 * Check app versions with given version.
	 *
	 * @param string $version   - String Version against which comparision to be done
	 * @param string $type
	 * @param string $condition - String Condition like ( '=', '!=', '<', '<=', '>', '>=')
	 *
	 * @return bool|int
	 */
	public static function check($version, $type = 'appVersion', $condition = '>=')
	{
		static::init();
		return static::compare($version, static::$versions[$type], $condition);
	}

	/**
	 * Compares two version number strings.
	 *
	 * @param string $v1
	 * @param string $v2
	 * @param string $operator
	 *
	 * @return mixed
	 */
	public static function compare($v1, $v2, $operator = '==')
	{
		if ('x' === substr($v2, -1)) {
			$ev2 = \explode('.', $v2);
			\array_pop($ev2);
			$lv2 = \count($ev2);
			$v2 = \implode('.', $ev2);
			$v1 = \implode('.', \array_slice(\explode('.', $v1), 0, $lv2));
		}
		return version_compare($v1, $v2, $operator);
	}
}
