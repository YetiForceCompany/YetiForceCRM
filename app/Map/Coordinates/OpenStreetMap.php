<?php
/**
 * Class to get coordinates for OpenStreetMap.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 *
 * @link      https://wiki.openstreetmap.org/wiki/Nominatim
 */

namespace App\Map\Coordinates;

/**
 * OpenStreetMap Connector to get coordinates.
 */
class OpenStreetMap extends Base
{
	/**
	 * {@inheritdoc}
	 */
	public function getCoordinates(array $addressInfo)
	{
		$coordinates = false;
		if (empty($addressInfo) || !\App\RequestUtil::isNetConnection()) {
			return $coordinates;
		}
		$url = \AppConfig::module('OpenStreetMap', 'ADDRESS_TO_SEARCH') . '/?';
		$data = [
			'format' => 'json',
			'addressdetails' => 1,
			'limit' => 1,
		];
		$url .= \http_build_query(array_merge($data, $addressInfo));
		try {
			$response = (new \GuzzleHttp\Client())->request('GET', $url, \App\RequestHttp::getOptions() + ['timeout' => 1]);
			if ($response->getStatusCode() === 200) {
				$coordinates = \App\Json::decode($response->getBody());
			} else {
				\App\Log::warning('Error with connection - ' . __CLASS__);
			}
		} catch (\Exception $ex) {
			\App\Log::warning('Error - ' . __CLASS__ . ' - ' . $ex->getMessage());
		}
		return $coordinates;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getCoordinatesByValue(string $value)
	{
		$coordinatesDetails = $this->getCoordinates(['q' => $value]);
		if ($coordinatesDetails) {
			$coordinatesDetails = reset($coordinatesDetails);
			return ['lat' => $coordinatesDetails['lat'], 'lon' => $coordinatesDetails['lon']];
		}
		return false;
	}
}
