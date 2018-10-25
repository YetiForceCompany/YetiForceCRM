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
	 * @throws \App\Exceptions\NoPermittedForAdmin
	 *
	 * @return array
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
		if (!static::isAllowedDirectory($catalogToRead)) {
			throw new \App\Exceptions\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
		}
		$catalogs = new \DirectoryIterator($catalogPath);
		foreach ($catalogs as $element) {
			$requestUrl = 'index.php?module=Backup&parent=Settings&view=Index';
			if ($element->isDot()) {
				if (!empty($catalogToReadArray)) {
					array_pop($catalogToReadArray);
					$parentUrl = implode(DIRECTORY_SEPARATOR, $catalogToReadArray);
					$returnStructure['manage'] = "$requestUrl&catalog=$parentUrl";
				}
			} else {
				$record['name'] = $element->getBasename();
				if (is_dir($catalogPath . DIRECTORY_SEPARATOR . $element)) {
					$record['directory'] = "$requestUrl&catalog=$urlDirectory$element";
					$returnStructure['catalogs'][] = $record;
				} else {
					if (!\in_array($element->getExtension(), static::getAllowedExtension())) {
						continue;
					}
					$record['directory'] = "$requestUrl&action=downloadFile&mode=download&file=$urlDirectory$element";
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
	 * @return array
	 */
	public static function getAllowedExtension()
	{
		return \AppConfig::module('Backup', 'EXT_TO_SHOW');
	}

	/**
	 * Check is it an allowed directory.
	 *
	 * @param string $dir
	 *
	 * @return bool
	 */
	public static function isAllowedDirectory(string $dir)
	{
		$fullPath = static::getBackupCatalogPath() . DIRECTORY_SEPARATOR . $dir;
		if (!is_readable($fullPath) || !is_dir($fullPath) || is_file($fullPath)) {
			return false;
		}
		return true;
	}

	/**
	 * Check is it an allowed file directory.
	 *
	 * @param string $dir
	 *
	 * @return bool
	 */
	public static function isAllowedFileDirectory(string $dir)
	{
		$fullPath = static::getBackupCatalogPath() . DIRECTORY_SEPARATOR . $dir;
		if (!is_readable($fullPath) || is_dir($fullPath) || !is_file($fullPath)) {
			return false;
		}
		return true;
	}
}
