<?php
/**
 * Config main class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App;

/**
 * Config main class.
 */
class Config
{
	/**
	 * Js environment variables.
	 *
	 * @var array
	 */
	private static $jsEnv = [];

	/**
	 * Get all js configuration in json.
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return string
	 */
	public static function getJsEnv()
	{
		return Json::encode(self::$jsEnv);
	}

	/**
	 * Set js environment variables.
	 *
	 * @param string $key
	 * @param mixed  $value
	 */
	public static function setJsEnv($key, $value)
	{
		self::$jsEnv[$key] = $value;
	}

	public static function main(?string $arg = null, $default = null)
	{
		if (isset($GLOBALS[$arg])) {
			return $GLOBALS[$arg];
		}
		$class = "\Config\Main";
		return self::get($class, $arg, $default);
	}

	public static function module(string $moduleName, ?string $arg = null, $default = null)
	{
		$class = "\Config\Modules\\$moduleName";
		return self::get($class, $arg, $default);
	}

	public static function performance(?string $arg = null, $default = null)
	{
		$class = "\Config\Performance";
		return self::get($class, $arg, $default);
	}

	public static function api(?string $arg = null, $default = null)
	{
		$class = "\Config\Api";
		return self::get($class, $arg, $default);
	}

	public static function debug(?string $arg = null, $default = null)
	{
		$class = "\Config\Debug";
		return self::get($class, $arg, $default);
	}

	public static function developer(?string $arg = null, $default = null)
	{
		$class = "\Config\Developer";
		return self::get($class, $arg, $default);
	}

	public static function get(string $class, ?string $arg = null, $default = null)
	{
		$value = $default;
		if ($arg === null) {
			$value = \class_exists($class) ? (new \ReflectionClass($class))->getStaticProperties() : [];
		} elseif (\class_exists($class) && isset($class::$$arg)) {
			$value = $class::$$arg;
		}
		return $value;
	}
}
