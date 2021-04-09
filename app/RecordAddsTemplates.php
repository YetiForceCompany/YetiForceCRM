<?php
/**
 * Record adds templates file.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
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
	 * @param string $className
	 *
	 * @return RecordAddsTemplates\Object
	 */
	public static function getInstance(string $className)
	{
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
					$instance = static::getInstance($fileName);
					$listTemplates[] = ['templateName' => $fileName, 'label' => $instance->label, 'icon' => $instance->icon];
				}
			}
		}
		return $listTemplates;
	}
}
