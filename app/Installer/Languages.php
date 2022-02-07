<?php
/**
 * Languages installer.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Installer;

/**
 * Class Languages.
 */
class Languages
{
	/**
	 * @var string
	 */
	private static $lastErrorMessage;

	/**
	 * Get updates to install.
	 *
	 * @return bool
	 */
	public static function getToInstall(): bool
	{
		$langs = self::getAll();
		foreach (\App\Language::getAll(true, true) as $key => $row) {
			if (isset($langs[$key]) && strtotime($langs[$key]['time']) > strtotime($row['lastupdated'])) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Get all languages for the current version.
	 *
	 * @return string[]
	 */
	public static function getAll()
	{
		if (!\App\RequestUtil::isNetConnection()) {
			\App\Log::warning('ERR_NO_INTERNET_CONNECTION', __METHOD__);
			return [];
		}
		$file = ROOT_DIRECTORY . '/app_data/LanguagesUpdater.json';
		if (\file_exists($file) && filemtime($file) > strtotime('-5 minute')) {
			return \App\Json::read($file);
		}
		$endpoint = \App\Config::developer('LANGUAGES_UPDATE_DEV_MODE') ? 'Developer' : \App\Version::get();
		$languages = [];
		try {
			$url = "https://github.com/YetiForceCompany/YetiForceCRMLanguages/raw/master/{$endpoint}/lang.json";
			\App\Log::beginProfile("GET|Languages::getAll|{$url}", __NAMESPACE__);
			$response = (new \GuzzleHttp\Client())->request('GET', $url, \App\RequestHttp::getOptions());
			\App\Log::endProfile("GET|Languages::getAll|{$url}", __NAMESPACE__);
			if (200 === $response->getStatusCode()) {
				$body = \App\Json::decode($response->getBody());
				if ($body) {
					\file_put_contents($file, $response->getBody());
					foreach ($body as $prefix => $row) {
						$languages[$prefix] = $row;
					}
				}
			}
		} catch (\Exception $ex) {
			\App\Log::warning($ex->__toString(), __CLASS__);
		}
		return $languages;
	}

	/**
	 * Download language.
	 *
	 * @param string $prefix
	 *
	 * @return bool true if success
	 */
	public static function download(string $prefix)
	{
		if (!\App\RequestUtil::isNetConnection()) {
			\App\Log::warning('ERR_NO_INTERNET_CONNECTION', __METHOD__);
			static::$lastErrorMessage = 'ERR_NO_INTERNET_CONNECTION';
			return false;
		}
		$endpoint = \App\Config::developer('LANGUAGES_UPDATE_DEV_MODE') ? 'Developer' : \App\Version::get();
		$url = "https://github.com/YetiForceCompany/YetiForceCRMLanguages/raw/master/{$endpoint}/{$prefix}.zip";
		$path = \App\Fields\File::getTmpPath() . $prefix . '.zip';
		$status = false;
		if (\App\Fields\File::isExistsUrl($url)) {
			try {
				\App\Log::beginProfile("GET|Languages::download|{$url}", __NAMESPACE__);
				(new \GuzzleHttp\Client(\App\RequestHttp::getOptions()))->request('GET', $url, ['sink' => $path]);
				\App\Log::endProfile("GET|Languages::download|{$url}", __NAMESPACE__);
				if (\file_exists($path)) {
					(new \vtlib\Language())->import($path);
					\unlink($path);
					$status = true;
				}
			} catch (\Exception $ex) {
				\App\Log::warning($ex->__toString(), __METHOD__);
				static::$lastErrorMessage = $ex->getMessage();
			}
		} else {
			static::$lastErrorMessage = 'ERR_CANNOT_PARSE_SERVER_RESPONSE';
		}
		return $status;
	}

	/**
	 * Get last error message.
	 *
	 * @return string|null
	 */
	public static function getLastErrorMessage(): ?string
	{
		return static::$lastErrorMessage;
	}

	/**
	 * Check if language exists.
	 *
	 * @param string $prefix
	 *
	 * @return bool
	 */
	public static function exists(string $prefix): bool
	{
		return \file_exists(ROOT_DIRECTORY . '/languages/' . $prefix . '/_Base.json');
	}
}
