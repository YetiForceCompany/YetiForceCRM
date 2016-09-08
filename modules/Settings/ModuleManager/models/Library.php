<?php

/**
 * Module Manager Library class
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_ModuleManager_Library_Model
{

	public static $dirs = [
		'mPDF' => ['dir' => 'libraries/mPDF/', 'url' => 'https://github.com/YetiForceCompany/lib_mPDF', 'name' => 'lib_mPDF'],
		'roundcube' => ['dir' => 'modules/OSSMail/roundcube/', 'url' => 'https://github.com/YetiForceCompany/lib_roundcube', 'name' => 'lib_roundcube'],
		'PHPExcel' => ['dir' => 'libraries/PHPExcel/', 'url' => 'https://github.com/YetiForceCompany/lib_PHPExcel', 'name' => 'lib_PHPExcel'],
		'AJAXChat' => ['dir' => 'libraries/AJAXChat/', 'url' => 'https://github.com/YetiForceCompany/lib_AJAXChat', 'name' => 'lib_AJAXChat']
	];
	public static $tempDir = 'cache' . DIRECTORY_SEPARATOR . 'upload';
	public static $missing = false;

	public static function getMissingLibrary()
	{
		$missing = self::$missing;
		if ($missing === false) {
			$missing = [];
			foreach (self::$dirs as $name => $lib) {
				if (!is_dir($lib['dir'])) {
					$missing[$name] = $lib;
				}
			}
			self::$missing = $missing;
		}
		return $missing;
	}

	public static function checkLibrary($name)
	{
		$missing = self::getMissingLibrary();
		return isset($missing[$name]);
	}

	public static function downloadAll()
	{
		foreach (self::getMissingLibrary() as $name => $lib) {
			self::download($name);
		}
	}

	public static function download($name, $update = false)
	{
		$mode = AppConfig::developer('MISSING_LIBRARY_DEV_MODE') ? 'developer' : 'master';
		$lib = self::$dirs[$name];
		$url = $lib['url'] . "/archive/$mode.zip";
		$path = self::$tempDir . DIRECTORY_SEPARATOR . $name . '.zip';
		$compressedName = $lib['name'] . '-' . $mode;

		if (!file_exists($path)) {
			if ($file = file_get_contents($url, false, stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]))) {
				file_put_contents($path, $file);
			}
		}
		if (file_exists($path) && filesize($path) > 0) {
			$unzip = new \vtlib\Unzip($path);
			$unzip->unzipAllEx('.', [], [ $compressedName => $lib['dir']]);
			$unzip->close();
		}
		unlink($path);
	}
}
