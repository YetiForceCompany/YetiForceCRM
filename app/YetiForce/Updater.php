<?php
/**
 * YetiForce updater class.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\YetiForce;

/**
 * YetiForce updater class.
 */
class Updater
{
	/**
	 * Get list of current updates.
	 *
	 * @return array
	 */
	public static function get(): array
	{
		$updaterDev = true;
		$updaterMode = $updaterDev ? 'developer' : 'master';
		$return = [];
		$fullVer = \explode('.', \App\Version::get());
		$ver = \array_shift($fullVer);
		$getVersion = $updaterDev ? ($ver . '.' . \array_shift($fullVer) . '.0') : \App\Version::get();
		try {
			$url = "https://github.com/YetiForceCompany/UpdatePackages/raw/{$updaterMode}/YetiForce%20CRM%20{$ver}.x.x/Updater.json";
			$response = (new \GuzzleHttp\Client(\App\RequestHttp::getOptions()))->request('GET', $url);
			if (200 !== $response->getStatusCode()) {
				throw new \App\Exceptions\AppException('Error with connection |' . $response->getStatusCode());
			}
			$body = $response->getBody();
			$body = \App\Json::isEmpty($body) ? [] : \App\Json::decode($body);
			if ($body && isset($body[$getVersion])) {
				$return = $body[$getVersion];
			}
		} catch (\Throwable $ex) {
			\App\Log::warning('Error - ' . __CLASS__ . ' - ' . $ex->getMessage());
		}
		return $return;
	}
}
