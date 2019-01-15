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
	 * Language repository url.
	 *
	 * @var string
	 */
	public static $githubUrl = 'https://api.github.com/repos/YetiForceCompany/YetiForceCRMLanguages';

	/**
	 * Get all languages for the current version.
	 *
	 * @return string[]
	 */
	public static function getAll()
	{
		$endpoint = \AppConfig::developer('LANGUAGES_UPDATE_DEV_MODE') ? 'Developer' : \App\Version::get();
		$languages = [];
		try {
			$response = (new \GuzzleHttp\Client())->request('GET', static::$githubUrl . '/contents/' . $endpoint, \App\RequestHttp::getOptions() + ['timeout' => 2]);
			if ($response->getStatusCode() === 200) {
				$body = \App\Json::decode($response->getBody());
				if ($body) {
					foreach ($body as $row) {
						$languages[$row['name']] = \App\Language::getDisplayName($row['name']);
					}
				}
			}
		} catch (\Exception $ex) {
			\App\Log::warning($ex->__toString(), __CLASS__);
		}
		return $languages;
	}
}
