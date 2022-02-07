<?php
/**
 * YetiForce updater class.
 * Modifying this file or functions that affect the footer appearance will violate the license terms!!!
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\YetiForce;

/**
 * YetiForce updater class.
 */
class Updater
{
	/**
	 * System version for updates.
	 *
	 * @var string
	 */
	private static $version = '';

	/**
	 * Get list of available updates.
	 *
	 * @return array
	 */
	public static function get(): array
	{
		$fullVer = \explode('.', \App\Version::get());
		array_pop($fullVer);
		self::$version = \implode('.', $fullVer);
		$file = ROOT_DIRECTORY . '/app_data/SystemUpdater.json';
		if (\file_exists($file) && filemtime($file) > strtotime('-5 minute')) {
			return \App\Json::read($file);
		}
		$return = [];
		$updaterMode = \Config\Developer::$updaterDevMode ? 'developer' : 'master';
		try {
			$url = "https://github.com/YetiForceCompany/UpdatePackages/raw/{$updaterMode}/YetiForce%20CRM%20{$fullVer[0]}.x.x/Updater.json";
			\App\Log::beginProfile("GET|Updater::get|{$url}", __NAMESPACE__);
			$response = (new \GuzzleHttp\Client(\App\RequestHttp::getOptions()))->request('GET', $url);
			\App\Log::endProfile("GET|Updater::get|{$url}", __NAMESPACE__);
			if (200 !== $response->getStatusCode()) {
				throw new \App\Exceptions\AppException('Error with connection |' . $response->getStatusCode());
			}
			$body = $response->getBody();
			$body = \App\Json::isEmpty($body) ? [] : \App\Json::decode($body);
			if ($body && isset($body[self::$version])) {
				$return = $body[self::$version];
				\App\Json::save($file, $return);
			}
		} catch (\Throwable $ex) {
			\App\Log::warning('Error - ' . __CLASS__ . ' - ' . $ex->getMessage());
		}
		return $return;
	}

	/**
	 * Get updates to install.
	 *
	 * @return array
	 */
	public static function getToInstall(): array
	{
		$data = self::get();
		if (\Config\Developer::$updaterDevMode) {
			$where = ['like', 'from_version',  self::$version . '.%', false];
		} else {
			$where = ['from_version' => \App\Version::get()];
			foreach ($data as $key => $row) {
				if ($row['fromVersion'] !== \App\Version::get()) {
					unset($data[$key]);
				}
			}
		}
		$query = (new \App\Db\Query())->from('yetiforce_updates')->where($where)->andWhere(['result' => 1]);

		$dataReader = $query->createCommand()->query();
		$updates = [];
		foreach ($dataReader as $row) {
			$updates[$row['name']] = $row;
		}
		foreach ($data as $key => &$row) {
			$row['hash'] = \md5($row['label']);
			if (isset($updates[$row['label']])) {
				unset($data[$key]);
			}
		}
		return $data;
	}

	/**
	 * Check if the package has been downloaded.
	 *
	 * @param string[] $package
	 *
	 * @return bool
	 */
	public static function isDownloaded(array $package): bool
	{
		return \file_exists(ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . \Settings_ModuleManager_Module_Model::getUploadDirectory() . \DIRECTORY_SEPARATOR . $package['hash'] . '.zip');
	}

	/**
	 * Download package.
	 *
	 * @param string[] $package
	 *
	 * @return void
	 */
	public static function download(array $package)
	{
		try {
			$uploadDir = \Settings_ModuleManager_Module_Model::getUploadDirectory();
			$path = ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . $uploadDir . \DIRECTORY_SEPARATOR . $package['hash'] . '.zip';
			$url = $package['url'];
			if (\Config\Developer::$updaterDevMode) {
				$url = \str_replace('raw/master', 'raw/developer', $url);
			}
			\App\Log::beginProfile("GET|Updater::download|{$url}", __NAMESPACE__);
			(new \GuzzleHttp\Client(\App\RequestHttp::getOptions()))->request('GET', $url, ['sink' => $path]);
			\App\Log::endProfile("GET|Updater::download|{$url}", __NAMESPACE__);
		} catch (\Throwable $ex) {
			\App\Log::warning('Error - ' . __CLASS__ . ' - ' . $ex->getMessage());
		}
	}
}
