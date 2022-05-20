<?php
/**
 * Cache main file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App;

/**
 * Cache main class.
 */
class Cache
{
	/** @var int Long time data storage */
	const LONG = 3600;

	/** @var int Medium time data storage */
	const MEDIUM = 300;

	/** @var int Short time data storage */
	const SHORT = 60;
	public static $pool;
	public static $staticPool;
	/**
	 * Clean the opcache after the script finishes.
	 *
	 * @var bool
	 */
	public static $clearOpcache = false;

	/**
	 * Initialize cache class.
	 */
	public static function init()
	{
		$driver = \App\Config::performance('CACHING_DRIVER');
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
	 *
	 * @param string $key       Cache ID
	 * @param mixed  $nameSpace
	 *
	 * @return mixed
	 */
	public static function get(string $nameSpace, string $key)
	{
		return static::$pool->get("$nameSpace-$key");
	}

	/**
	 * Confirms if the cache contains specified cache item.
	 *
	 * @param string $nameSpace
	 * @param string $key       Cache ID
	 *
	 * @return bool
	 */
	public static function has(string $nameSpace, string $key): bool
	{
		return static::$pool->has("$nameSpace-$key");
	}

	/**
	 * Cache Save.
	 *
	 * @param string $key       Cache ID
	 * @param mixed  $value     Data to store, supports string, array, objects
	 * @param int    $duration  Cache TTL (in seconds)
	 * @param mixed  $nameSpace
	 *
	 * @return bool
	 */
	public static function save(string $nameSpace, string $key, $value = null, $duration = self::MEDIUM)
	{
		if (!static::$pool->save("$nameSpace-$key", $value, $duration)) {
			Log::warning("Error writing to cache. Key: $nameSpace-$key | Value: " . var_export($value, true));
		}
		return $value;
	}

	/**
	 * Removes the item from the cache.
	 *
	 * @param string $key       Cache ID
	 * @param mixed  $nameSpace
	 *
	 * @return bool
	 */
	public static function delete(string $nameSpace, string $key)
	{
		static::$pool->delete("$nameSpace-$key");
	}

	/**
	 * Deletes all items in the cache.
	 *
	 * @return bool
	 */
	public static function clear(): bool
	{
		return static::$pool->clear();
	}

	/**
	 * Returns a static Cache Item representing the specified key.
	 *
	 * @param string $nameSpace
	 * @param string $key       Cache ID
	 *
	 * @return mixed
	 */
	public static function staticGet(string $nameSpace, string $key = '')
	{
		return static::$staticPool->get("$nameSpace-$key");
	}

	/**
	 * Confirms if the static cache contains specified cache item.
	 *
	 * @param string $nameSpace
	 * @param string $key       Cache ID
	 *
	 * @return bool
	 */
	public static function staticHas(string $nameSpace, string $key = '')
	{
		return static::$staticPool->has("$nameSpace-$key");
	}

	/**
	 * Static cache save.
	 *
	 * @param string $nameSpace
	 * @param string $key       Cache ID
	 * @param mixed  $value     Data to store
	 * @param int    $duration  Cache TTL (in seconds)
	 *
	 * @return bool
	 */
	public static function staticSave(string $nameSpace, string $key, $value = null)
	{
		return static::$staticPool->save("$nameSpace-$key", $value);
	}

	/**
	 * Removes the item from the static cache.
	 *
	 * @param string $nameSpace
	 * @param string $key       Cache ID
	 *
	 * @return bool
	 */
	public static function staticDelete(string $nameSpace, string $key)
	{
		static::$staticPool->delete("$nameSpace-$key");
	}

	/**
	 * Deletes all items in the static cache.
	 *
	 * @return bool
	 */
	public static function staticClear()
	{
		static::$staticPool->clear();
	}

	/**
	 * Clear the opcache after the script finishes.
	 *
	 * @return bool
	 */
	public static function clearOpcache(): bool
	{
		if (static::$clearOpcache) {
			return false;
		}
		register_shutdown_function(function () {
			try {
				static::resetOpcache();
			} catch (\Throwable $e) {
				\App\Log::error($e->getMessage() . PHP_EOL . $e->__toString());
			}
		});
		return static::$clearOpcache = true;
	}

	/**
	 * Reset opcache if it is possible.
	 *
	 * @return void
	 */
	public static function resetOpcache(): void
	{
		if (\function_exists('opcache_reset')) {
			\opcache_reset();
		}
	}

	/**
	 * Reset file from opcache if it is possible.
	 *
	 * @param string $path
	 *
	 * @return void
	 */
	public static function resetFileCache(string $path): void
	{
		if (\function_exists('opcache_invalidate')) {
			\opcache_invalidate($path, true);
		}
	}

	/**
	 * Clear all cache.
	 *
	 * @return void
	 */
	public static function clearAll(): void
	{
		static::clearOpcache();
		static::clear();
		clearstatcache();
	}

	/**
	 * Clean old cache files.
	 *
	 * @param string $days
	 *
	 * @return int[]
	 */
	public static function clearTemporaryFiles(string $days = '-30 day'): array
	{
		$time = strtotime($days);
		$exclusion = ['.htaccess', 'index.html'];
		$s = $i = 0;
		foreach (['pdf', 'import', 'mail', 'vtlib', 'rss_cache', 'upload', 'templates_c'] as $dir) {
			foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(ROOT_DIRECTORY . "/cache/{$dir}", \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST) as $item) {
				if ($item->isFile() && !\in_array($item->getBasename(), $exclusion) && $item->getMTime() < $time && $item->getATime() < $time) {
					$s += $item->getSize();
					unlink($item->getPathname());
					++$i;
				}
			}
		}
		foreach ([ROOT_DIRECTORY . '/cache', \App\Fields\File::getTmpPath()] as $dir) {
			foreach ((new \DirectoryIterator($dir)) as $item) {
				if ($item->isFile() && 'index.html' !== $item->getBasename() && $item->getMTime() < $time && $item->getATime() < $time) {
					$s += $item->getSize();
					unlink($item->getPathname());
					++$i;
				}
			}
		}
		return ['size' => $s, 'counter' => $i];
	}
}
