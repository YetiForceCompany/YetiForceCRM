<?php namespace includes;

/**
 * System warnings basic class
 * @package YetiForce.Include
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class SystemWarnings
{

	const FOLDERS = 'include/SystemWarnings';
	const SELECTED_FOLDERS = ['SystemRequirements'];

	/**
	 * Returns a list of folders warnings
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
				$folder = ['id' => $i, 'text' => vtranslate($fileName, 'Settings:SystemWarnings'), 'subPath' => $subPath, 'parent' => '#'];
				if (isset($folders[$parent])) {
					$folder['parent'] = $folders[$parent]['id'];
				}
				if (in_array($subPath, self::SELECTED_FOLDERS)) {
					$folder['state']['selected'] = true;
				}
				$folders[$subPath] = $folder;
			}
			$i++;
		}
		return $folders;
	}

	/**
	 * Returns a list of warnings instance
	 * @param array $folders
	 * @return array
	 */
	public static function getWarnings($folders, $active)
	{
		if (empty($folders)) {
			return [];
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
					$className = "\includes\SystemWarnings\\$folder\\$fileName";
					$instace = new $className;
					if ($instace->preProcess()) {
						$isIgnored = $instace->getStatus() == 2;
						$show = true;
						if (!$isIgnored) {
							$instace->process();
						}
						if ($isIgnored && $active) {
							$show = false;
						}
						if ($show) {
							$instace->setFolder($folder);
							$actions[] = $instace;
						}
					}
				}
			}
		}
		return $actions;
	}

	/**
	 * Returns number of warnings
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
				$className = "\includes\SystemWarnings\\$folder\\$fileName";
				$instace = new $className;
				if ($instace->preProcess()) {
					if ($instace->getStatus() != 2) {
						$instace->process();
					}
					if ($instace->getStatus() == 0) {
						$i++;
					}
				}
			}
		}
		return $i;
	}

	/**
	 * Update ignoring status
	 * @param string $id
	 * @param int $status
	 * @return boolean
	 */
	public static function setIgnored($id, $status)
	{
		$loader = \ComposerAutoloaderInit::getLoader();
		$file = $loader->findFile('\\' . $id);

		if (!$file) {
			return false;
		}
		$status = $status == '2' ? 0 : 2;

		$fileContent = file_get_contents($file);
		if (strpos($fileContent, 'protected $status ') !== false) {
			$pattern = '/\$status = ([^;]+)/';
			$replacement = '$status = ' . $status;
			$fileContent = preg_replace($pattern, $replacement, $fileContent);
		} else {
			$replacement = '{' . PHP_EOL . '	protected $status = ' . $status . ';';
			$fileContent = preg_replace('/{/', $replacement, $fileContent, 1);
		}
		file_put_contents($file, $fileContent);
		return true;
	}
}
