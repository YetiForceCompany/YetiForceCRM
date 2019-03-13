<?php
/**
 * Languages installer.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
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
		$endpoint = \App\Config::developer('LANGUAGES_UPDATE_DEV_MODE') ? 'Developer' : \App\Version::get();
		$languages = [];
		try {
			$response = (new \GuzzleHttp\Client())->request('GET', "https://github.com/YetiForceCompany/YetiForceCRMLanguages/raw/master/{$endpoint}/lang.json", \App\RequestHttp::getOptions());
			if ($response->getStatusCode() === 200) {
				$body = \App\Json::decode($response->getBody());
				if ($body) {
					foreach ($body as $prefix => $row) {
						$languages[$prefix] = \array_merge($row, [
							'name' => \App\Language::getDisplayName($prefix),
							'exist' => \file_exists(\ROOT_DIRECTORY . "/languages/{$prefix}/_Base.json")
						]);
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
				(new \GuzzleHttp\Client(\App\RequestHttp::getOptions()))->request('GET', $url, ['sink' => $path]);
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
	 * @return null|string
	 */
	public static function getLastErrorMessage(): ?string
	{
		return static::$lastErrorMessage;
	}
}
