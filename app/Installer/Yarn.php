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
		'' => []
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
		$yarnPackage = ROOT_DIRECTORY . DIRECTORY_SEPARATOR . 'package.json';
		if (file_exists($yarnPackage)) {
			$rootDir = realpath(ROOT_DIRECTORY) . DIRECTORY_SEPARATOR . 'public_html' . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR;
			$package = \App\Json::decode(file_get_contents($yarnPackage));
			foreach ($package['dependencies'] as $key => $val) {
				$pathToClear = $rootDir . DIRECTORY_SEPARATOR . $key;
				//echo $key . ' -> ' . $pathToClear . "\r\n";
				//echo $key . ' -> ' . "\r\n";
				if (file_exists($pathToClear)) {
					foreach (new \DirectoryIterator($pathToClear) as $level1) {
						/*if ($level1->isDir()) {
							echo "\t\\" . $level1 . "\r\n";
						} else {
							echo "\t" . $level1 . "\r\n";
						}*/
					}
					//echo "\t" . \App\Installer\Yarn::getDirSize($pathToClear) . "\r\n";
					static::cleanDirOther($pathToClear);
				}
			}
		} else {
			echo "$yarnPackage";
			throw new \TypeError('The package.json file is missing');
		}
	}

	private static function cleanDirOther($path)
	{
		foreach (static::$packageCleanMapForOther as $item) {
			$fileName = $path . \DIRECTORY_SEPARATOR . $item['name'];
			if (\file_exists($fileName) && \is_dir($fileName)=== $item['isDir']) {
				if ($item['isDir']) {
					echo "\t DEL: $fileName \r\n";
					echo "\t\t" . \vtlib\Functions::showBytes(static::getDirSize($fileName)) . "\r\n";
				} else {
					echo "\t $item[name] -> $fileName \r\n";
				}

				//\vtlib\Functions::recurseDelete($fileName, true);
			}
		}
	}

	public static function getDirSize($path)
	{
		$size = 0;
		foreach (new \DirectoryIterator($path) as $dirObj) {
			if ($dirObj->isDir() && !$dirObj->isDot()) {
				$size += static::getDirSize($dirObj->getRealPath());
			} else {
				$size += \filesize($dirObj->getPathname());
			}
		}
		return $size;
	}
}
