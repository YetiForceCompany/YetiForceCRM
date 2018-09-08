<?php
/**
 * Class to get coordinates for OpenStreetMap.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
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
		$url = \AppConfig::module('OpenStreetMap', 'ADDRESS_TO_SEARCH') . '/?';
		$data = [
			'format' => 'json',
			'addressdetails' => 1,
			'limit' => 1,
		];
		$coordinates = false;
		if (empty($addressInfo)) {
			return $coordinates;
		}
		$url .= \http_build_query(array_merge($data, $addressInfo));
		try {
			$response = \Requests::get($url);
			if ($response->success) {
				$coordinates =  \App\Json::decode($response->body);
			}
		} catch (\Exception $ex) {
			\App\Log::warning($ex->getMessage());
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
