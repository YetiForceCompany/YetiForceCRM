<?php
/**
 * Address finder class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Map;

/**
 * Custom colors stylesheet file generator.
 */
class Address
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
	 * @var Address\Base[]
	 */
	private static $providerInstanceCache = [];

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
	 * Get provider for address finder.
	 *
	 * @return string[]
	 */
	public static function getProvider()
	{
		if (self::$providersCache) {
			return self::$providersCache;
		}
		$dir = new \DirectoryIterator(\ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . 'app/Map/Address');
		foreach ($dir as $fileinfo) {
			if ($fileinfo->getExtension() === 'php' && ($fileName = $fileinfo->getBasename('.php')) !== 'Base' && static::getInstance($fileName)->isActive()) {
				self::$providersCache[] = $fileName;
			}
		}
		return self::$providersCache;
	}

	/**
	 * Get address finder instance by type.
	 *
	 * @param string $type
	 *
	 * @return \App\Map\Address\Base
	 */
	public static function getInstance($type)
	{
		if (isset(self::$providerInstanceCache[$type])) {
			return self::$providerInstanceCache[$type];
		}
		$className = "\App\Map\Address\\$type";
		return self::$providerInstanceCache[$type] = new $className();
	}

	/**
	 * Get config for address finder.
	 *
	 * @return array
	 */
	public static function getConfig()
	{
		if (\App\Cache::has('AddressFinder', 'Config')) {
			return \App\Cache::get('AddressFinder', 'Config');
		}
		$query = (new \App\Db\Query())->from('s_#__address_finder_config');
		$dataReader = $query->createCommand()->query();
		$config = [];
		while ($row = $dataReader->read()) {
			$config[$row['type']][$row['name']] = $row['val'];
		}
		\App\Cache::save('AddressFinder', 'Config', $config, \App\Cache::LONG);
		return $config;
	}
}
