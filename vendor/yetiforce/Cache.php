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
	public static $staticPool;

	const LONG = 3600;
	const MEDIUM = 300;
	const SHORT = 60;

	/**
	 * Initialize cache class.
	 */
	public static function init()
	{
		$driver = \AppConfig::performance('CACHING_DRIVER');
		static::$staticPool = new \App\Cache\Base();
		if ($driver) {
			$className = '\App\Cache\\' . $driver;
			static::$pool = new $className();
			return;
		}
		static::$pool = static::$staticPool;
	}

	/**
	 * Returns a Cache Item representing the specified key.
	 * @param string $key Cache ID
	 * @return string|array
	 */
	public static function get($nameSpace, $key)
	{
		return static::$pool->get("$nameSpace-$key");
	}

	/**
	 * Confirms if the cache contains specified cache item.
	 * @param string $key Cache ID
	 * @return bool
	 */
	public static function has($nameSpace, $key)
	{
		return static::$pool->has("$nameSpace-$key");
	}

	/**
	 * Cache Save
	 * @param string $key Cache ID
	 * @param mixed $value Data to store, supports string, array, objects
	 * @param int $duration Cache TTL (in seconds)
	 * @return bool
	 */
	public static function save($nameSpace, $key, $value = null, $duration = self::MEDIUM)
	{
		return static::$pool->save("$nameSpace-$key", $value, $duration);
	}

	/**
	 * Removes the item from the cache.
	 * @param string $key Cache ID
	 * @return bool
	 */
	public static function delete($nameSpace, $key)
	{
		static::$pool->delete("$nameSpace-$key");
	}

	/**
	 * Deletes all items in the cache.
	 * @return bool
	 */
	public static function clear()
	{
		static::$pool->clear();
	}

	/**
	 * Returns a static Cache Item representing the specified key.
	 * @param string $nameSpace
	 * @param string $key Cache ID
	 * @return string|array
	 */
	public static function staticGet($nameSpace, $key)
	{
		return static::$staticPool->get("$nameSpace-$key");
	}

	/**
	 * Confirms if the static cache contains specified cache item.
	 * @param string $nameSpace
	 * @param string $key Cache ID
	 * @return bool
	 */
	public static function staticHas($nameSpace, $key)
	{
		return static::$staticPool->has("$nameSpace-$key");
	}

	/**
	 * Static cache save
	 * @param string $nameSpace
	 * @param string $key Cache ID
	 * @param string $value Data to store
	 * @param int $duration Cache TTL (in seconds)
	 * @return bool
	 */
	public static function staticSave($nameSpace, $key, $value = null)
	{
		return static::$staticPool->save("$nameSpace-$key", $value);
	}

	/**
	 * Removes the item from the static cache.
	 * @param string $nameSpace
	 * @param string $key Cache ID
	 * @return bool
	 */
	public static function staticDelete($nameSpace, $key)
	{
		static::$staticPool->delete("$nameSpace-$key");
	}

	/**
	 * Deletes all items in the static cache.
	 * @return bool
	 */
	public static function staticClear()
	{
		static::$staticPool->clear();
	}
}
