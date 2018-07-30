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
		'screenshot' => ['isDir' => false, 'screenshot.jpg']
	];

	public static function runEvent($yarnEvent)
	{
		switch ($yarnEvent) {
			case 'postinstall':
				static::eventPostInstall();
				return;
			case 'preinstall':
				return;
		}
		throw new \TypeError("Unknown '$yarnEvent' yarn event");
	}

	public static function eventPostInstall()
	{
		//$rootDir = realpath(__DIR__ . '/../../') . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR;
		$v = var_export($argv, true);
		$str = 'eventInstall: ' . date('Y-m-d') . ' - ' . $v . "\r\n";
		//$str .= "$rootDir" . "\r\n";
		file_put_contents('C:\\www\\YetiForceCRM\\test.txt', $str, FILE_APPEND);
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
				echo $key . ' -> ' . "\r\n";
				if (file_exists($pathToClear)) {
					foreach (new \DirectoryIterator($pathToClear) as $level1) {
						if ($level1->isDir()) {
							echo "\t\\" . $level1 . "\r\n";
						} else {
							echo "\t" . $level1 . "\r\n";
						}
					}
					//echo "\t" . \App\Installer\Yarn::getDirSize($pathToClear) . "\r\n";
					//static::cleanDirOther($pathToClear);
				}
			}
		} else {
			echo "$yarnPackage";
			throw new \TypeError('The package.json file is missing');
		}
	}

	private static function cleanDirOther($path)
	{
		/*foreach (new \DirectoryIterator($path) as $objDir) {
			if (!$objDir->isDot()) {
			}
		}*/
		foreach (static::$packageCleanMapForOther as $item) {
			$fileName = $path . \DIRECTORY_SEPARATOR . $item;
			if (\file_exists($fileName)) {
				echo "\t DEL: $fileName \r\n";

				echo "\t" . \vtlib\Functions::showBytes(static::getDirSize($fileName)) . "\r\n";
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
