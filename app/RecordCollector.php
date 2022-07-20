<?php
/**
 * Record collector file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App;

/**
 * Record collector class.
 */
class RecordCollector
{
	/**
	 * Get record collector instance.
	 *
	 * @param string $className
	 * @param string $moduleName
	 *
	 * @return RecordCollectors\Base|null
	 */
	public static function getInstance(string $className, string $moduleName): ?RecordCollectors\Base
	{
		$instance = null;
		if (class_exists($className) && is_subclass_of($className, 'App\RecordCollectors\Base')) {
			$instance = new $className();
			$instance->moduleName = $moduleName;
		}
		return $instance;
	}

	/**
	 * Get active record collector by type.
	 *
	 * @param string $displayType
	 * @param string $moduleName
	 *
	 * @return RecordCollectors\Base[]
	 */
	public static function getAllByType(string $displayType, string $moduleName): array
	{
		$recordCollector = [];
		foreach ((new \DirectoryIterator(__DIR__ . '/RecordCollectors')) as $fileinfo) {
			if ('php' === $fileinfo->getExtension() && 'Base' !== ($fileName = $fileinfo->getBasename('.php'))) {
				$instance = self::getInstance('App\RecordCollectors\\' . $fileName, $moduleName);
				if ($instance->isActive() && $instance->displayType === $displayType) {
					$recordCollector[$fileName] = $instance;
				}
			}
		}
		return $recordCollector;
	}
}
