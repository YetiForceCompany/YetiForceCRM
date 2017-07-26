<?php
namespace App;

/**
 * Session class
 * @package YetiForce.App
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Adrian Koń <a.kon@yetiforce.com>
 */
class Session
{

	/**
	 *
	 * @var type 
	 */
	public static $pool;

	/**
	 * Initialize session class.
	 */
	public static function init()
	{
		$driver = \AppConfig::performance('SESSION_DRIVER');
		static::$staticPool = new \App\Cache\File();
		if ($driver) {
			$className = '\App\Cache\\' . $driver;
			static::$pool = new $className();
			return;
		}
		static::$pool = static::$staticPool;
	}
}
