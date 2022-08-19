<?php
/**
 * Address finder class.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\Map;

/**
 * Custom colors stylesheet file generator.
 */
class Address
{
	/** @var \App\Map\Address\Base[] Providers cache. */
	private static $providersCache = [];

	/** @var string[] Active providers cache. */
	private static $activeProvidersCache = [];

	/** @var Address\Base[] Providers instance cache. */
	private static $providerInstanceCache = [];

	/**
	 * Get default provider.
	 *
	 * @return string
	 */
	public static function getDefaultProvider(): string
	{
		$defaultProvider = static::getConfig()['global']['default_provider'] ?? '';
		if (!$defaultProvider) {
			$provider = static::getActiveProviders();
			if ($provider) {
				$defaultProvider = \current($provider);
			}
		}
		return $defaultProvider;
	}

	/**
	 * Get active providers for address finder.
	 *
	 * @return string[]
	 */
	public static function getActiveProviders(): array
	{
		if (self::$activeProvidersCache) {
			return self::$activeProvidersCache;
		}
		if (self::$providersCache) {
			foreach (self::$providersCache as $provider) {
				if ($provider->isActive()) {
					self::$activeProvidersCache[] = $provider->getName();
				}
			}
		} else {
			foreach (self::getAllProviders() as $provider) {
				if ($provider->isActive()) {
					self::$activeProvidersCache[] = $provider->getName();
				}
			}
		}
		return self::$activeProvidersCache;
	}

	/**
	 * Get all providers for address finder.
	 *
	 * @return \App\Map\Address\Base[]
	 */
	public static function getAllProviders(): array
	{
		if (self::$providersCache) {
			return self::$providersCache;
		}
		foreach ((new \DirectoryIterator(ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . 'app/Map/Address')) as $fileinfo) {
			if ('php' === $fileinfo->getExtension() && 'Base' !== ($fileName = $fileinfo->getBasename('.php'))) {
				self::$providersCache[$fileName] = static::getInstance($fileName);
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
	public static function getInstance($type): Address\Base
	{
		if (isset(self::$providerInstanceCache[$type])) {
			return self::$providerInstanceCache[$type];
		}
		$className = "\\App\\Map\\Address\\$type";
		return self::$providerInstanceCache[$type] = new $className();
	}

	/**
	 * Get config for address finder.
	 *
	 * @return array
	 */
	public static function getConfig(): array
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
