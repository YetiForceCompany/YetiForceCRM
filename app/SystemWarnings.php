<?php

namespace App;

/**
 * System warnings basic class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class SystemWarnings
{
	const FOLDERS = 'app/SystemWarnings';
	const SELECTED_FOLDERS = ['SystemRequirements', 'YetiForce', 'Security'];

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
				$subPath = str_replace(DIRECTORY_SEPARATOR, '/', $subPath);
				$parent = rtrim(rtrim($subPath, $fileName), '/');
				$folder = ['id' => $i, 'text' => Language::translate($fileName, 'Settings:SystemWarnings'), 'subPath' => $subPath, 'parent' => '#'];
				if (isset($folders[$parent])) {
					$folder['parent'] = $folders[$parent]['id'];
				}
				if (in_array($subPath, self::SELECTED_FOLDERS)) {
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
	 *
	 * @return array
	 */
	public static function getWarnings($folders, $active = true)
	{
		if (empty($folders)) {
			return [];
		}
		if (!is_array($folders) && $folders === 'all') {
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
					$className = "\App\SystemWarnings\\$folder\\$fileName";
					$instace = new $className();
					if ($instace->preProcess()) {
						$isIgnored = $instace->getStatusValue() === 2;
						$show = true;
						if (!$isIgnored) {
							$instace->process();
						}
						if ($active && ($isIgnored || $instace->getStatus() === 1)) {
							$show = false;
						}
						if ($show) {
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

	/**
	 * Returns number of warnings.
	 *
	 * @return int
	 */
	public static function getWarningsCount()
	{
		$i = 0;
		foreach ($iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(self::FOLDERS, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST) as $item) {
			if ($item->isFile() && $item->getBasename('.php') != 'Template') {
				$subPath = $iterator->getSubPath();
				$fileName = $item->getBasename('.php');
				$folder = str_replace('/', '\\', $subPath);
				$className = "\App\SystemWarnings\\$folder\\$fileName";
				$instace = new $className();
				if ($instace->preProcess()) {
					if ($instace->getStatus() != 2) {
						$instace->process();
					}
					if ($instace->getStatus() == 0) {
						++$i;
					}
				}
			}
		}
		return $i;
	}
}
