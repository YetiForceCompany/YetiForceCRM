<?php
/**
 * Config main class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App;

/**
 * Config main class.
 */
class Config
{
	/**
	 * Requesrt start time.
	 *
	 * @var int
	 */
	public static $startTime;

	/**
	 * Request mode.
	 *
	 * @var string
	 */
	public static $requestMode;

	/**
	 * CRM root directory.
	 *
	 * @var string
	 */
	public static $rootDirectory;

	/**
	 * Request process type.
	 *
	 * @var string
	 */
	public static $processType;

	/**
	 * Request process name.
	 *
	 * @var string
	 */
	public static $processName;

	/**
	 * Js configuratin.
	 *
	 * @var array
	 */
	private static $jsConf = [];

	/**
	 * Set js configuratin.
	 *
	 * @param string $key
	 * @param mixed  $value
	 */
	public static function setJs($key, $value)
	{
		static::$jsConf[$key] = $value;
	}

	/**
	 * Get all js configuratin in json.
	 *
	 * @return type
	 */
	public static function getJs()
	{
		return Json::encode(static::$jsConf);
	}
}
