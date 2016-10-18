<?php
namespace App;

/**
 * Cache main class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Cache
{

	public static $pool;

	/**
	 * Initialize cache class.
	 */
	public static function init()
	{
		$driver = \AppConfig::performance('CACHING_DRIVER');
		if (empty($driver)) {
			$driver = 'Base';
		}
		$className = '\App\Cache\\' . $driver;
		static::$pool = new $className();
	}

	/**
	 * Returns a Cache Item representing the specified key.
	 * @param string|array $key Cache ID
	 * @return string|array
	 */
	public static function get($key)
	{
		return static::$pool->get($key);
	}

	/**
	 * Confirms if the cache contains specified cache item.
	 * @param string|array $key Cache ID
	 * @return bool
	 */
	public static function has($key)
	{
		return static::$pool->has($key);
	}

	/**
	 * Cache Save
	 * @param string $key Cache ID
	 * @param string|array $value Data to store
	 * @param int $duration Cache TTL (in seconds)
	 * @return bool
	 */
	public static function save($key, $value = null, $duration)
	{
		return static::$pool->save($key, $value, $duration);
	}

	/**
	 * Removes the item from the cache.
	 * @param string|array $key Cache ID
	 * @return bool
	 */
	public static function delete($key)
	{
		static::$pool->delete($key);
	}

	/**
	 * Deletes all items in the cache.
	 * @return bool
	 */
	public static function clear()
	{
		static::$pool->clear();
	}
}
