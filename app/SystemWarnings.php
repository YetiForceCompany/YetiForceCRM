<?php
/**
 * System warnings basic file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App;

/**
 * System warnings basic class.
 */
class SystemWarnings
{
	const FOLDERS = 'app/SystemWarnings';
	const SELECTED_FOLDERS = ['SystemRequirements', 'YetiForce', 'Security', 'Mail'];

	/**
	 * Returns a list of folders warnings.
	 *
	 * @return array
	 */
	public static function getFolders()
	{
		$folders = [];
		$i = 0;
		foreach ($iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(self::FOLDERS, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST) as $item) {
			if ($item->isDir()) {
				$subPath = $iterator->getSubPathName();
				$fileName = $item->getFilename();
				$subPath = str_replace(\DIRECTORY_SEPARATOR, '/', $subPath);
				$parent = rtrim(rtrim($subPath, $fileName), '/');
				$folder = ['id' => $i, 'text' => Language::translate($fileName, 'Settings:SystemWarnings'), 'subPath' => $subPath, 'parent' => '#'];
				if (isset($folders[$parent])) {
					$folder['parent'] = $folders[$parent]['id'];
				}
				if (\in_array($subPath, self::SELECTED_FOLDERS)) {
					$folder['state']['selected'] = true;
				}
				$folders[$subPath] = $folder;
			}
			++$i;
		}
		return $folders;
	}

	/**
	 * Returns a list of warnings instance.
	 *
	 * @param array $folders
	 * @param mixed $active
	 *
	 * @return array
	 */
	public static function getWarnings($folders, $active = true)
	{
		if (empty($folders)) {
			return [];
		}
		if (!\is_array($folders) && 'all' === $folders) {
			$folders = array_keys(static::getFolders());
		}
		$actions = [];
		foreach ($folders as $folder) {
			$dir = self::FOLDERS . '/' . $folder;
			if (!is_dir($dir)) {
				continue;
			}
			$iterator = new \DirectoryIterator($dir);
			foreach ($iterator as $item) {
				if (!$item->isDot() && !$item->isDir()) {
					$fileName = $item->getBasename('.php');
					$folder = str_replace('/', '\\', $folder);
					$className = "\\App\\SystemWarnings\\$folder\\$fileName";
					$instace = new $className();
					if ($instace->preProcess()) {
						$isIgnored = 2 === $instace->getStatusValue();
						if (!$active || !$isIgnored) {
							$instace->process();
						}
						if (!$active || (!$isIgnored && 1 !== $instace->getStatus())) {
							$instace->setFolder($folder);
							$actions[$instace->getPriority() . $fileName] = $instace;
						}
					}
				}
			}
		}
		krsort($actions);

		return $actions;
	}
}
