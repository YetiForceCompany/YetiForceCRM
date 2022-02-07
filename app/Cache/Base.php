<?php
/**
 * Base caching file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\Cache;

/**
 * Base caching class.
 */
class Base
{
	private static $cache = [];

	/**
	 * Is apcu is available.
	 *
	 * @return bool
	 */
	public static function isSupported()
	{
		return true;
	}

	/**
	 * Returns a cache item representing the specified key.
	 *
	 * @param array|string $key Cache ID
	 *
	 * @return array|string
	 */
	public function get($key)
	{
		return self::$cache[$key] ?? false;
	}

	/**
	 * Confirms if the cache contains specified cache item.
	 *
	 * @param array|string $key Cache ID
	 *
	 * @return bool
	 */
	public function has($key)
	{
		return isset(self::$cache[$key]);
	}

	/**
	 * Cache save.
	 *
	 * @param string            $key      Cache ID
	 * @param array|string|null $value    Data to store
	 * @param false|int         $duration Cache TTL (in seconds)
	 *
	 * @return bool
	 */
	public function save($key, $value = null, $duration = false)
	{
		self::$cache[$key] = $value;
		unset($duration);
		return true;
	}

	/**
	 * Removes the item from the cache.
	 *
	 * @param array|string $key Cache ID
	 *
	 * @return bool
	 */
	public function delete($key)
	{
		unset(self::$cache[$key]);
	}

	/**
	 * Deletes all items in the cache.
	 *
	 * @return bool
	 */
	public function clear()
	{
		self::$cache = [];
		return true;
	}
}
