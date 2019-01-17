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
		$endpoint = \AppConfig::developer('LANGUAGES_UPDATE_DEV_MODE') ? 'Developer' : \App\Version::get();
		$languages = [];
		try {
			$response = (new \GuzzleHttp\Client())->request('GET', 'https://api.github.com/repos/YetiForceCompany/YetiForceCRMLanguages/contents/' . $endpoint, \App\RequestHttp::getOptions());
			if ($response->getStatusCode() === 200) {
				$body = \App\Json::decode($response->getBody());
				if ($body) {
					foreach ($body as $row) {
						if ($row['type'] === 'file') {
							$prefix = pathinfo($row['name'], \PATHINFO_FILENAME);
							$languages[$prefix] = [
								'name' => \App\Language::getDisplayName($prefix),
								'exist' => \is_dir(\ROOT_DIRECTORY . "/languages/{$prefix}")
							];
						}
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
	 * @return array
	 */
	public static function download(string $prefix)
	{
		if (!\App\RequestUtil::isNetConnection()) {
			\App\Log::warning('ERR_NO_INTERNET_CONNECTION', __METHOD__);
			return false;
		}
		$endpoint = \AppConfig::developer('LANGUAGES_UPDATE_DEV_MODE') ? 'Developer' : \App\Version::get();
		$url = "https://github.com/YetiForceCompany/YetiForceCRMLanguages/raw/master/{$endpoint}/{$prefix}.zip";
		$path = \App\Fields\File::getTmpPath() . $prefix . '.zip';
		$status = false;
		try {
			(new \GuzzleHttp\Client(\App\RequestHttp::getOptions()))->request('GET', $url, ['sink' => $path]);
			if (\file_exists($path)) {
				(new \vtlib\Language())->import($path);
				\unlink($path);
				$status = true;
			}
		} catch (\Exception $ex) {
			\App\Log::warning($ex->__toString(), __METHOD__);
		}
		return $status;
	}
}
