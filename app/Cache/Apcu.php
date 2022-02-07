<?php
/**
 * APC caching file.
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
 * APC caching class.
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
		return \function_exists('apcu_enabled') && apcu_enabled();
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
		return apcu_fetch($key);
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
		return apcu_exists($key);
	}

	/**
	 * Cache save.
	 *
	 * @param string       $key      Cache ID
	 * @param array|string $value    Data to store
	 * @param int          $duration Cache TTL (in seconds)
	 *
	 * @return array|bool
	 */
	public function save($key, $value, $duration)
	{
		return apcu_store($key, $value, $duration);
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
