<?php
namespace App\Cache;

use App\Exceptions\CacheException;

/**
 * XCache Caching Class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class XCache
{

	/**
	 * Is apcu is available
	 * @return bool
	 */
	public static function isSupported()
	{
		return extension_loaded('xcache');
	}

	/**
	 * Class constructor
	 * @throws CacheException
	 */
	public function __construct()
	{
		if (!static::isSupported()) {
			throw new CacheException('XCache is not enabled');
		}
	}

	/**
	 * Returns a Cache Item representing the specified key.
	 * @param string|array $key Cache ID
	 * @return string|array
	 */
	public function get($key)
	{
		return xcache_get($key);
	}

	/**
	 * Confirms if the cache contains specified cache item.
	 * @param string|array $key Cache ID
	 * @return bool
	 */
	public function has($key)
	{
		return xcache_isset($key);
	}

	/**
	 * Cache Save
	 * @param string $key Cache ID
	 * @param string|array $value Data to store
	 * @param int $duration Cache TTL (in seconds)
	 * @return bool
	 */
	public function save($key, $value = null, $duration)
	{
		return xcache_set($key, $value, $duration);
	}

	/**
	 * Removes the item from the cache.
	 * @param string|array $key Cache ID
	 * @return bool
	 */
	public function delete($key)
	{
		return xcache_unset($key);
	}

	/**
	 * Deletes all items in the cache.
	 * @return bool
	 */
	public function clear()
	{
		for ($i = 0, $max = xcache_count(XC_TYPE_VAR); $i < $max; $i++) {
			if (xcache_clear_cache(XC_TYPE_VAR, $i) === false) {
				return false;
			}
		}
		return true;
	}
}
