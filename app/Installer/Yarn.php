<?php

namespace App\Installer;

/**
 * Yarn installer.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */
class Yarn
{
	/**
	 * List of files to be removed depending on the package.
	 *
	 * @var array
	 */
	public static $packageCleanMap = [
		'jquery-gantt-editor' => ['cleanDirOther'=>true, 'toRemove'=>[
			'gantt.html' => ['isDir' => false, 'name' => 'gantt.html'],
		]],
		'jQuery-Validation-Engine' => ['cleanDirOther' => true, 'toRemove' => [
			'releases.html' => ['isDir' => false, 'name' => 'releases.html'],
			'runDemo.bat' => ['isDir' => false, 'name' => 'runDemo.bat'],
			'runDemo.sh' => ['isDir' => false, 'name' => 'runDemo.sh'],
		]],
		'leaflet.markercluster' => ['cleanDirOther' => true, 'toRemove' => [
			'ISSUE_TEMPLATE.md' => ['isDir' => false, 'name' => 'ISSUE_TEMPLATE.md'],
		]],
		'updated-jqplot' => ['cleanDirOther' => true, 'toRemove' => [
			'optionsTutorial.txt' => ['isDir' => false, 'name' => 'optionsTutorial.txt'],
			'usage.txt' => ['isDir' => false, 'name' => 'usage.txt'],
			'jqPlotCssStyling.txt' => ['isDir' => false, 'name' => 'jqPlotCssStyling.txt'],
			'jqPlotOptions.txt' => ['isDir' => false, 'name' => 'jqPlotOptions.txt'],
		]],
		'split.js' => ['cleanDirOther' => true, 'toRemove' => [
			'logo.svg' => ['isDir' => false, 'name' => 'logo.svg'],
		]],
	];

	/**
	 * The list of files to remove common to all packages.
	 *
	 * @var array
	 */
	public static $packageCleanMapForOther = [
		'src' => ['isDir'=>true, 'name'=>'src'],
		'demo' => ['isDir' => true, 'name' =>'demo'],
		'demos' => ['isDir' => true, 'name' =>'demos'],
		'.idea' => ['isDir' => true, 'name' =>'.idea'],
		'sample' => ['isDir' => true, 'name' => 'sample'],
		'samples' => ['isDir' => true, 'name' => 'samples'],
		//Files
		'screenshot' => ['isDir' => false, 'name' =>'screenshot.jpg'],
		'demo.html' => ['isDir' => false, 'name' =>'demo.html'],
		'index.html' => ['isDir' => false, 'name' =>'index.html'],
		'example.html' => ['isDir' => false, 'name' =>'example.html'],
		'example.png' => ['isDir' => false, 'name' =>'example.png'],
		'example.png' => ['test.js' => false, 'name' =>'test.js'],
		'favicon.ico' => ['favicon.ico' => false, 'name' => 'favicon.ico'],
	];

	/**
	 * List of files to be deleted recursively.
	 *
	 * @var array
	 */
	public static $packageCleanMapRecurse = [
		'readme.md' => ['isDir' => false, 'name' => 'readme.md'],
		'readme.txt' => ['isDir' => false, 'name' => 'readme.txt'],
		'test.html' => ['isDir' => false, 'name' => 'test.html'],
	];

	/**
	 * Call the method depending on the Yarn event.
	 *
	 * @param string $yarnEvent
	 */
	public static function runEvent($yarnEvent)
	{
		switch ($yarnEvent) {
			case 'postinstall':
				static::clean();
				return;
		}
		throw new \TypeError("Unknown '$yarnEvent' yarn event");
	}

	/**
	 * Cleaning packages.
	 *
	 * @throws \App\Exceptions\AppException
	 */
	public static function clean()
	{
		$yarnPackage = ROOT_DIRECTORY . DIRECTORY_SEPARATOR . 'package.json';
		if (static::fileExists($yarnPackage)) {
			$rootDir = realpath(ROOT_DIRECTORY) . DIRECTORY_SEPARATOR . 'public_html' . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR;
			$package = \App\Json::decode(file_get_contents($yarnPackage));

			foreach ($package['dependencies'] as $key => $val) {
				$pathToClear = $rootDir . DIRECTORY_SEPARATOR . $key;
				if (static::fileExists($pathToClear)) {
					if (isset(static::$packageCleanMap[$key])) {
						static::cleanDir($pathToClear, $key);
					} else {
						static::cleanDirOther($pathToClear, static::$packageCleanMapForOther);
					}
					static::cleanRecurse($pathToClear, static::$packageCleanMapRecurse);
				}
			}
		} else {
			echo "$yarnPackage";
			throw new \TypeError('The package.json file is missing');
		}
	}

	/**
	 * Clearing the folder for the package.
	 *
	 * @param string $path
	 * @param string $packageName
	 */
	private static function cleanDir($path, $packageName)
	{
		$item = static::$packageCleanMap[$packageName];
		static::cleanDirOther($path, $item['toRemove']);
		if (!isset($item['cleanDirOther']) || $item['cleanDirOther']) {
			static::cleanDirOther($path, static::$packageCleanMapForOther);
		}
	}

	/**
	 * Cleaning folder common to all.
	 *
	 * @param string $path
	 * @param []     $packageMap
	 */
	private static function cleanDirOther($path, $packageMap)
	{
		foreach ($packageMap as $item) {
			$fileName = $path . \DIRECTORY_SEPARATOR . $item['name'];
			if (($realFileName = static::fileExists($fileName))!==false && \is_dir($realFileName) === $item['isDir']) {
				\vtlib\Functions::recurseDelete($realFileName, true);
			}
		}
	}

	/**
	 * Cleaning the recursive folder.
	 *
	 * @param string $path
	 * @param []     $packageMap
	 */
	public static function cleanRecurse($path, $packageMap)
	{
		if (static::fileExists($path)) {
			static::cleanDirOther($path, $packageMap);
			foreach (new \DirectoryIterator($path) as $obj) {
				if ($obj->isDir() && !$obj->isDot()) {
					static::cleanRecurse($obj->getRealPath(), $packageMap);
				}
			}
		}
	}

	/**
	 * Checking if the file exists with ignore case.
	 *
	 * @param string $fileName
	 *
	 * @return bool|string
	 */
	private static function fileExists($fileName)
	{
		if (file_exists($fileName)) {
			return $fileName;
		}
		$lowerfile = strtolower($fileName);
		foreach (glob(dirname($fileName) . '/*') as $file) {
			if (strtolower($file) === $lowerfile) {
				return $file;
			}
		}
		return false;
	}

	/**
	 * Recursive calculation of the folder size.
	 *
	 * @param string $path
	 *
	 * @return int
	 */
	public static function getDirSize($path)
	{
		$size = 0;
		if (\is_dir($path)) {
			foreach (new \DirectoryIterator($path) as $dirObj) {
				if ($dirObj->isDir() && !$dirObj->isDot()) {
					$size += static::getDirSize($dirObj->getRealPath());
				} else {
					$size += \filesize($dirObj->getPathname());
				}
			}
		} else {
			$size = \filesize($path);
		}
		return $size;
	}

	public static function showDirYarn()
	{
		$yarnPackage = ROOT_DIRECTORY . DIRECTORY_SEPARATOR . 'package.json';
		if (static::fileExists($yarnPackage)) {
			$rootDir = realpath(ROOT_DIRECTORY) . DIRECTORY_SEPARATOR . 'public_html' . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR;
			$package = \App\Json::decode(file_get_contents($yarnPackage));

			foreach ($package['dependencies'] as $key => $val) {
				$path = $rootDir . DIRECTORY_SEPARATOR . $key;
				static::showDir($path);
			}
		} else {
			echo "$yarnPackage";
			throw new \TypeError('The package.json file is missing');
		}
	}

	public static function showDir($path, $tab = '')
	{
		echo $tab . '\\' . \basename($path) . "\r\n";
		if (static::fileExists($path)) {
			foreach (new \DirectoryIterator($path) as $obj) {
				if ($obj->isDir()) {
					if (!$obj->isDot()) {
						static::showDir($obj->getRealPath(), $tab . "\t");
					}
				} else {
					echo $tab . "\t" . $obj . "\r\n";
				}
			}
		}
	}
}
