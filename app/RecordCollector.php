<?php
/**
 * Record collector file.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
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
		if (is_subclass_of($className, 'App\RecordCollectors\Base')) {
			$instance = new $className();
			$instance->moduleName = $moduleName;
		}
		return $instance;
	}
}
