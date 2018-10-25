<?php
/**
 * Backup class.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 */

namespace App\Utils;

/**
 * Backup.
 */
class Backup
{
	/**
	 * Read catalog with backup files and return catalogs and files list.
	 *
	 * @param string $catalogToRead
	 * @param string $module
	 *
	 * @throws \App\Exceptions\NoPermitted
	 *
	 * @return array[]
	 */
	public static function readCatalog(string $catalogToRead)
	{
		$catalogPath = static::getBackupCatalogPath();
		$catalogToReadArray = $returnStructure = [];
		if (empty($catalogPath)) {
			return [];
		}
		$urlDirectory = '';
		if (!empty($catalogToRead)) {
			$catalogToReadArray = explode(DIRECTORY_SEPARATOR, $catalogToRead);
			$catalogPath .= DIRECTORY_SEPARATOR . $catalogToRead;
			$urlDirectory = $catalogToRead . DIRECTORY_SEPARATOR;
		}
		if (!static::isAllowedDirectory($catalogPath)) {
			throw new \App\Exceptions\NoPermitted('ERR_PERMISSION_DENIED');
		}
		$catalogs = new \DirectoryIterator($catalogPath);
		$allowedExtensions = static::getAllowedExtension();
		foreach ($catalogs as $element) {
			$requestUrl = 'index.php?module=Backup&parent=Settings&view=Index';
			if ($element->isDot()) {
				if (!empty($catalogToReadArray) && empty($returnStructure['manage'])) {
					array_pop($catalogToReadArray);
					$parentUrl = implode(DIRECTORY_SEPARATOR, $catalogToReadArray);
					$returnStructure['manage'] = "{$requestUrl}&catalog={$parentUrl}";
				}
			} else {
				$record['name'] = $element->getBasename();
				if ($element->isDir()) {
					$record['directory'] = "{$requestUrl}&catalog={$urlDirectory}{$record['name']}";
					$returnStructure['catalogs'][] = $record;
				} else {
					if (!\in_array($element->getExtension(), $allowedExtensions)) {
						continue;
					}
					$record['directory'] = "{$requestUrl}&action=downloadFile&file={$urlDirectory}{$record['name']}";
					$record['date'] = \App\Fields\DateTime::formatToDisplay(date('Y-m-d H:i:s', $element->getMTime()));
					$record['size'] = \vtlib\Functions::showBytes($element->getSize());
					$returnStructure['files'][] = $record;
				}
				unset($record);
			}
		}
		return $returnStructure;
	}

	/**
	 * Return catalog with backup files.
	 *
	 * @return string
	 */
	public static function getBackupCatalogPath()
	{
		return \AppConfig::module('Backup', 'BACKUP_PATH');
	}

	/**
	 * Return allowed extension of backup file.
	 *
	 * @return string[]
	 */
	public static function getAllowedExtension()
	{
		return \AppConfig::module('Backup', 'EXT_TO_SHOW');
	}

	/**
	 * Check is it an allowed directory.
	 *
	 * @param string $fullPath
	 *
	 * @return bool
	 */
	public static function isAllowedDirectory(string $fullPath)
	{
		return !(!is_readable($fullPath) || !is_dir($fullPath) || is_file($fullPath));
	}

	/**
	 * Check is it an allowed file directory.
	 *
	 * @param string $fullPath
	 *
	 * @return bool
	 */
	public static function isAllowedFileDirectory(string $fullPath)
	{
		return !(!is_readable($fullPath) || is_dir($fullPath) || !is_file($fullPath));
	}
}
