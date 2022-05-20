<?php

/**
 * Module Manager Library file.
 *
 * @package   Model
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
/**
 * Module Manager Library class.
 */
class Settings_ModuleManager_Library_Model
{
	/**
	 * List of all installation libraries.
	 *
	 * @var array
	 */
	public static $libraries = [
		'roundcube' => ['dir' => 'public_html/modules/OSSMail/roundcube/', 'url' => 'https://github.com/YetiForceCompany/lib_roundcube', 'name' => 'lib_roundcube'],
	];

	/**
	 * Path to save temporary files.
	 *
	 * @var string
	 */
	const TEMP_DIR = 'cache' . DIRECTORY_SEPARATOR . 'upload';

	/**
	 * Function to check library status.
	 *
	 * @param string $name
	 *
	 * @return bool
	 */
	public static function checkLibrary($name)
	{
		if (App\Cache::has('checkLibrary', $name)) {
			return App\Cache::get('checkLibrary', $name);
		}
		$status = true;
		if (static::$libraries[$name]) {
			$lib = static::$libraries[$name];
			if (file_exists($lib['dir'] . 'version.php')) {
				$libVersion = require $lib['dir'] . 'version.php';
				if (App\Version::check($libVersion['version'], $lib['name'])) {
					$status = false;
				}
			}
		}
		App\Cache::save('checkLibrary', $name, $status, App\Cache::LONG);
		return $status;
	}

	/**
	 * Get a list of all libraries and their statuses.
	 *
	 * @return array
	 */
	public static function &getAll()
	{
		$libs = [];
		foreach (static::$libraries as $name => $lib) {
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
			$libs[$name] = $lib;
		}
		return $libs;
	}

	/**
	 * Download all missing libraries.
	 */
	public static function downloadAll()
	{
		foreach (static::$libraries as $name => &$lib) {
			static::download($name);
		}
	}

	/**
	 * Function to download library.
	 *
	 * @param string $name
	 *
	 * @throws \App\Exceptions\AppException
	 * @throws \App\Exceptions\NoPermitted
	 *
	 * @return bool
	 */
	public static function download(string $name): bool
	{
		$returnVal = true;
		if (!static::$libraries[$name]) {
			App\Log::warning('Library does not exist: ' . $name, 'Library');
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		$lib = static::$libraries[$name];
		$path = static::TEMP_DIR . DIRECTORY_SEPARATOR . $lib['name'] . '.zip';
		$mode = \App\Config::developer('MISSING_LIBRARY_DEV_MODE') ? 'developer' : App\Version::get($lib['name']);
		$compressedName = $lib['name'] . '-' . $mode;
		if (!file_exists($path) && \App\RequestUtil::isNetConnection()) {
			$url = $lib['url'] . "/archive/$mode.zip";
			App\Log::trace('Started downloading library: ' . $name, 'Library');
			try {
				\App\Log::beginProfile("GET|Library::download|{$url}", 'Library');
				(new \GuzzleHttp\Client(\App\RequestHttp::getOptions()))->request('GET', $url, ['sink' => $path]);
				\App\Log::endProfile("GET|Library::download|{$url}", 'Library');
				if (\file_exists($path)) {
					App\Log::trace('Completed downloads library: ' . $name, 'Library');
				} else {
					App\Log::warning('Can not connect to the server' . $url, 'Library');
				}
			} catch (\Exception $ex) {
				\App\Log::warning($ex->__toString(), __METHOD__, 'Library');
			}
		}
		if (file_exists($path) && filesize($path) > 0) {
			\vtlib\Functions::recurseDelete($lib['dir']);
			$zip = \App\Zip::openFile($path, ['checkFiles' => false]);
			$zip->unzip([$compressedName => $lib['dir']]);
			unlink($path);
			if ('roundcube' === $name) {
				$db = \App\Db::getInstance();
				foreach (['roundcube_cache', 'roundcube_cache_index', 'roundcube_cache_messages', 'roundcube_cache_shared', 'roundcube_cache_thread'] as $table) {
					$db->createCommand()->truncateTable($table)->execute();
				}
			}
		} else {
			App\Log::warning('No import file: ' . $name, 'Library');
			$returnVal = false;
		}
		return $returnVal;
	}

	/**
	 * Function to update library.
	 *
	 * @param string $name
	 */
	public static function update($name)
	{
		static::download($name);
	}
}
