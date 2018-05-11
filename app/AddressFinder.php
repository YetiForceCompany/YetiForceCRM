<?php
/**
 * Address finder class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App;

/**
 * Custom colors stylesheet file generator.
 */
class AddressFinder
{
	/**
	 * Providers cache.
	 *
	 * @var string[]
	 */
	private static $providersCache = [];
	/**
	 * Providers instance cache.
	 *
	 * @var AddressFinder\Base[]
	 */
	private static $providerInstanceCache = [];

	/**
	 * Get provider for address finder.
	 *
	 * @return string[]
	 */
	public static function getProvider()
	{
		if (static::$providersCache) {
			return static::$providersCache;
		}
		$dir = new \DirectoryIterator(\ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . 'app/AddressFinder');
		foreach ($dir as $fileinfo) {
			if ($fileinfo->getExtension() === 'php' && ($fileName = $fileinfo->getBasename('.php')) !== 'Base') {
				if (static::getInstance($fileName)->isActive()) {
					static::$providersCache[] = $fileName;
				}
			}
		}
		return static::$providersCache;
	}

	/**
	 * Get default provider.
	 *
	 * @return string[]
	 */
	public static function getDefaultProvider()
	{
		$provider = static::getProvider();
		if ($provider) {
			return \array_pop($provider);
		}
		return '';
	}

	/**
	 * Get address finder instance by type.
	 *
	 * @param string $type
	 *
	 * @return \App\AddressFinder\Base
	 */
	public static function getInstance($type)
	{
		if (isset(static::$providerInstanceCache[$type])) {
			return static::$providerInstanceCache[$type];
		}
		$className = "\App\AddressFinder\\$type";
		return static::$providerInstanceCache[$type] = new $className();
	}

	/**
	 * Get config for address finder.
	 *
	 * @return array
	 */
	public static function getConfig()
	{
		if (Cache::has('AddressFinder', 'Config')) {
			return Cache::get('AddressFinder', 'Config');
		}
		$query = (new \App\Db\Query())->from('s_#__address_finder_config');
		$dataReader = $query->createCommand()->query();
		$config = [];
		while ($row = $dataReader->read()) {
			$config[$row['type']][$row['name']] = $row['val'];
		}
		Cache::save('AddressFinder', 'Config', $config, Cache::LONG);
		return $config;
	}
}
