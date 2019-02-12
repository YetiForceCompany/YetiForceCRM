<?php

namespace App\Cache;

use App\Exceptions\CacheException;

/**
 * APC caching class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Apcu
{
	/**
	 * Class constructor.
	 *
	 * @throws CacheException
	 */
	public function __construct()
	{
		if (!static::isSupported()) {
			throw new CacheException('APCu is not enabled');
		}
	}

	/**
	 * Is apcu is available.
	 *
	 * @return bool
	 */
	public static function isSupported()
	{
		return function_exists('apcu_enabled') && apcu_enabled();
	}

	/**
	 * Returns a cache item representing the specified key.
	 *
	 * @param string|array $key Cache ID
	 *
	 * @return string|array
	 */
	public function get($key)
	{
		return apcu_fetch($key);
	}

	/**
	 * Confirms if the cache contains specified cache item.
	 *
	 * @param string|array $key Cache ID
	 *
	 * @return bool
	 */
	public function has($key)
	{
		return apcu_exists($key);
	}

	/**
	 * Cache save.
	 *
	 * @param string       $key      Cache ID
	 * @param string|array $value    Data to store
	 * @param int          $duration Cache TTL (in seconds)
	 *
	 * @return bool|array
	 */
	public function save($key, $value, $duration)
	{
		return apcu_store($key, $value, $duration);
	}

	/**
	 * Removes the item from the cache.
	 *
	 * @param string|array $key Cache ID
	 *
	 * @return bool
	 */
	public function delete($key)
	{
		return apcu_delete($key);
	}

	/**
	 * Deletes all items in the cache.
	 *
	 * @return bool
	 */
	public function clear()
	{
		return apcu_clear_cache();
	}
}
