<?php
/**
 * XCache caching file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Cache;

use App\Exceptions\CacheException;

/**
 * XCache caching class.
 */
class XCache
{
	/**
	 * Class constructor.
	 *
	 * @throws CacheException
	 */
	public function __construct()
	{
		if (!static::isSupported()) {
			throw new CacheException('XCache is not enabled');
		}
	}

	/**
	 * Is apcu is available.
	 *
	 * @return bool
	 */
	public static function isSupported()
	{
		return \extension_loaded('xcache');
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
		return xcache_get($key);
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
		return xcache_isset($key);
	}

	/**
	 * Cache save.
	 *
	 * @param string       $key      Cache ID
	 * @param array|string $value    Data to store
	 * @param int          $duration Cache TTL (in seconds)
	 *
	 * @return bool
	 */
	public function save($key, $value, $duration)
	{
		return xcache_set($key, $value, $duration);
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
		return xcache_unset($key);
	}

	/**
	 * Deletes all items in the cache.
	 *
	 * @return bool
	 */
	public function clear()
	{
		for ($i = 0, $max = xcache_count(XC_TYPE_VAR); $i < $max; ++$i) {
			if (false === xcache_clear_cache(XC_TYPE_VAR, $i)) {
				return false;
			}
		}
		return true;
	}
}
