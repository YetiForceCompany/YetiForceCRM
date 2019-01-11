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
}
