<?php
/**
 * Nominatim driver file to get coordinates.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 *
 * @see      https://wiki.openstreetmap.org/wiki/Nominatim
 */

namespace App\Map\Coordinates;

/**
 * Nominatim driver class to get coordinates.
 */
class Nominatim extends Base
{
	/** {@inheritdoc} */
	public function getCoordinates(array $addressInfo)
	{
		$coordinates = false;
		if (empty($addressInfo) || !\App\RequestUtil::isNetConnection()) {
			return $coordinates;
		}
		$url = $this->url . '/?' . \http_build_query(array_merge([
			'format' => 'json',
			'addressdetails' => 1,
			'limit' => 1,
		], $addressInfo));
		try {
			\App\Log::beginProfile("GET|Nominatim::getCoordinates|{$url}", __NAMESPACE__);
			$response = (new \GuzzleHttp\Client(\App\RequestHttp::getOptions()))->request('GET', $url);
			\App\Log::endProfile("GET|Nominatim::getCoordinates|{$url}", __NAMESPACE__);
			if (200 === $response->getStatusCode()) {
				$coordinates = \App\Json::decode($response->getBody());
			} else {
				throw new \App\Exceptions\AppException('Error with connection |' . $response->getReasonPhrase() . '|' . $response->getBody());
			}
		} catch (\Exception $ex) {
			\App\Log::error('Error - ' . $ex->getMessage(), __CLASS__);
		}
		return $coordinates;
	}

	/** {@inheritdoc} */
	public function getCoordinatesByValue(string $value): array
	{
		if ($coordinatesDetails = $this->getCoordinates(['q' => $value])) {
			$coordinatesDetails = reset($coordinatesDetails);
			return ['lat' => $coordinatesDetails['lat'], 'lon' => $coordinatesDetails['lon']];
		}
		return [];
	}
}
