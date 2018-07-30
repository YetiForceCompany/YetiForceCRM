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
	];

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

	public static $packageCleanMapRecurse = [
		'readme.md' => ['isDir' => false, 'name' => 'readme.md'],
		'test.html' => ['isDir' => false, 'name' => 'test.html'],
		'readme.txt' => ['isDir' => false, 'name' => 'readme.txt'],
	];

	public static function runEvent($yarnEvent)
	{
		switch ($yarnEvent) {
			case 'postinstall':
				static::clean();
				return;
		}
		throw new \TypeError("Unknown '$yarnEvent' yarn event");
	}

	public static function clean()
	{
		$str = 'eventInstall: ' . date('Y-m-d H:i:s') . "\r\n";
		file_put_contents('C:\\www\\YetiForceCRM\\test.txt', $str, FILE_APPEND);

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

	public static function showDir($path, $tab='')
	{
		echo $tab . '\\' . \basename($path) . "\r\n";
		if (static::fileExists($path)) {
			foreach (new \DirectoryIterator($path) as $obj) {
				if ($obj->isDir()) {
					//echo $tab . "\t\\" . $obj . "\r\n";
					if (!$obj->isDot()) {
						static::showDir($obj->getRealPath(), $tab . "\t");
					}
				} else {
					echo $tab . "\t" . $obj . "\r\n";
				}
			}
		}
	}

	private static function cleanDir($path, $packageName)
	{
		echo "$packageName\r\n";
		$item = static::$packageCleanMap[$packageName];
		static::cleanDirOther($path, $item['toRemove']);

		if (!isset($item['cleanDirOther']) || $item['cleanDirOther']) {
			echo 'CALL: cleanDirOther' . "\r\n";
			static::cleanDirOther($path, static::$packageCleanMapForOther);
		}
	}

	private static function cleanDirOther($path, $packageMap)
	{
		foreach ($packageMap as $item) {
			$fileName = $path . \DIRECTORY_SEPARATOR . $item['name'];
			if (($realFileName = static::fileExists($fileName))!==false && \is_dir($realFileName) === $item['isDir']) {
				if ($item['isDir']) {
					echo "\t DEL: $fileName \r\n";
					echo "\t\t" . \vtlib\Functions::showBytes(static::getDirSize($fileName)) . "\r\n";
				} else {
					echo "\t $item[name] -> $fileName \r\n";
					echo "\t $item[name] -> $realFileName \r\n";
					echo "\t\t" . \vtlib\Functions::showBytes(static::getDirSize($fileName)) . "\r\n";
				}

				\vtlib\Functions::recurseDelete($realFileName, true);
			}
		}
	}

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
			\filesize($path);
		}
		return $size;
	}
}
