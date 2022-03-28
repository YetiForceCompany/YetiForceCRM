<?php
/**
 * Backup class.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
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
			$catalogToReadArray = explode(\DIRECTORY_SEPARATOR, $catalogToRead);
			$catalogPath .= \DIRECTORY_SEPARATOR . $catalogToRead;
			$urlDirectory = $catalogToRead . \DIRECTORY_SEPARATOR;
		}
		if (!\App\Fields\File::isAllowedDirectory($catalogPath)) {
			throw new \App\Exceptions\NoPermitted('ERR_PERMISSION_DENIED');
		}
		$allowedExtensions = static::getAllowedExtension();
		$requestUrl = 'index.php?module=Backup&parent=Settings&view=Index';
		foreach ((new \DirectoryIterator($catalogPath)) as $element) {
			if ($element->isDot()) {
				if (!empty($catalogToReadArray) && empty($returnStructure['manage'])) {
					array_pop($catalogToReadArray);
					$parentUrl = implode(\DIRECTORY_SEPARATOR, $catalogToReadArray);
					$returnStructure['manage'] = "{$requestUrl}&catalog=" . rawurlencode($parentUrl);
				}
			} else {
				$record = [
					'name' => $element->getBasename(),
				];
				if ($element->isDir()) {
					if (!$element->isReadable() || !\App\Validator::dirName($element->getBasename())) {
						continue;
					}
					$record['url'] = "{$requestUrl}&catalog=" . rawurlencode($urlDirectory . $record['name']);
					$returnStructure['catalogs'][] = $record;
				} else {
					if (!$element->isReadable() || !\in_array($element->getExtension(), $allowedExtensions) || !\App\Validator::dirName($element->getBasename())) {
						continue;
					}
					$record['url'] = "{$requestUrl}&action=DownloadFile&file=" . rawurlencode($urlDirectory . $record['name']);
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
		return \App\Config::component('Backup', 'BACKUP_PATH');
	}

	/**
	 * Return allowed extension of backup file.
	 *
	 * @return string[]
	 */
	public static function getAllowedExtension()
	{
		return \App\Config::component('Backup', 'EXT_TO_SHOW');
	}
}
