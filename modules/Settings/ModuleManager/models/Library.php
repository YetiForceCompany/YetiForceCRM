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
	public static $cache = [];

	public static function checkLibrary($name)
	{
		if (isset(static::$cache[$name])) {
			return static::$cache[$name];
		}
		$status = true;
		if (isset(static::$dirs[$name])) {
			$lib = static::$dirs[$name];
			if (file_exists($lib['dir'] . 'version.php')) {
				$libVersion = require $lib['dir'] . 'version.php';
				if (App\Version::check($libVersion['version'], $lib['name'])) {
					$status = false;
				}
			}
		}
		static::$cache[$name] = $status;
		return $status;
	}

	public static function &getAll()
	{
		foreach (static::$dirs as $name => &$lib) {
			$status = 0;
			if (is_dir($lib['dir'])) {
				$status = 2;
				if (file_exists($lib['dir'] . 'version.php')) {
					$libVersion = require $lib['dir'] . 'version.php';
					if (App\Version::check($libVersion['version'], $lib['name'])) {
						$status = 1;
					}
				}
			}
			$lib['status'] = $status;
		}
		return static::$dirs;
	}

	public static function downloadAll()
	{
		foreach (static::$dirs as $name => &$lib) {
			static::download($name);
		}
	}

	public static function download($name)
	{
		if (!isset(static::$dirs[$name])) {
			App\Log::warning('Library does not exist: ' . $name);
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}
		$mode = AppConfig::developer('MISSING_LIBRARY_DEV_MODE') ? 'developer' : 'master';
		$lib = static::$dirs[$name];
		if (file_exists($lib['dir'] . 'version.php')) {
			App\Log::info('Library has already been downloaded: ' . $name);
			return false;
		}
		$url = $lib['url'] . "/archive/$mode.zip";
		$path = static::$tempDir . DIRECTORY_SEPARATOR . $name . '.zip';
		$compressedName = $lib['name'] . '-' . $mode;
		if (!file_exists($path)) {
			stream_context_set_default([
				'ssl' => [
					'verify_peer' => false,
					'verify_peer_name' => false,
				],
			]);
			$headers = get_headers($url, 1);
			if (isset($headers['Status']) && strpos($headers['Status'], '302') !== false) {
				App\Log::trace('Started downloading library: ' . $name);
				if ($file = file_get_contents($url, false, stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]))) {
					file_put_contents($path, $file);
					App\Log::trace('Completed downloads library: ' . $name);
				}
			} else {
				App\Log::warning('Can not connect to the server' . $url);
			}
		}
		if (file_exists($path) && filesize($path) > 0) {
			$unzip = new \vtlib\Unzip($path);
			$unzip->unzipAllEx('.', [], [ $compressedName => $lib['dir']]);
			$unzip->close();
			unlink($path);
		} else {
			App\Log::warning('No import file: ' . $name);
		}
	}

	public static function update($name)
	{
		$lib = static::$dirs[$name];
		\vtlib\Functions::recurseDelete($lib['dir']);
		static::download($name);
	}
}
