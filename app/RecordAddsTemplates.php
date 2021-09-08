<?php
/**
 * Record adds templates file.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Sołek <a.solek@yetiforce.com>
 */

namespace App;

/**
 * Record adds templates class.
 */
class RecordAddsTemplates
{
	/**
	 * Get record adds templates instance.
	 *
	 * @param string $name
	 *
	 * @return object
	 */
	public static function getInstance(string $name)
	{
		$className = '\\App\\RecordAddsTemplates\\' . $name;
		return new $className();
	}

	/**
	 * List of available templates.
	 *
	 * @return array
	 */
	public static function getTemplatesList(): array
	{
		$listTemplates = [];
		$pathDirectory = ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . 'app' . \DIRECTORY_SEPARATOR . 'RecordAddsTemplates';
		if (file_exists($pathDirectory)) {
			foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($pathDirectory, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST) as $item) {
				if ($item->isFile()) {
					$fileName = $item->getBasename('.php');
					$listTemplates[] = static::getInstance($fileName);
				}
			}
		}
		return $listTemplates;
	}
}
