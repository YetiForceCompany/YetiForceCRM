<?php
/**
 * File connector to find routing. Connector based on service Project-OSRM.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 *
 * @see       http://project-osrm.org
 * @see       https://github.com/Project-OSRM/osrm-backend
 */

namespace App\Map\Routing;

/**
 * Class connector for service Project-OSRM to get routing.
 */
class Osrm extends Base
{
	/** {@inheritdoc} */
	public function calculate()
	{
		if (!\App\RequestUtil::isNetConnection()) {
			throw new \App\Exceptions\AppException('ERR_NO_INTERNET_CONNECTION');
		}
		$url = $this->url . '/route/v1/car/' . implode(';', $this->parsePoints()) . '?' . \http_build_query([
			'geometries' => 'geojson',
			'steps' => 'true',
		]);
		\App\Log::beginProfile("GET|OsrmRouting::calculate|{$url}", __NAMESPACE__);
		$response = (new \GuzzleHttp\Client(\App\RequestHttp::getOptions()))->request('GET', $url, [
			'timeout' => 120,
			'http_errors' => false,
		]);
		\App\Log::endProfile("GET|OsrmRouting::calculate|{$url}", __NAMESPACE__);
		if (200 === $response->getStatusCode()) {
			$json = \App\Json::decode($response->getBody());
		} else {
			throw new \App\Exceptions\AppException('Error with connection |' . $response->getReasonPhrase() . '|' . $response->getBody(), 500);
		}
		$coordinates = [];
		if (!empty($json['routes'])) {
			foreach ($json['routes'] as $route) {
				$coordinates = array_merge($coordinates, $route['geometry']['coordinates']);
				$this->distance += $route['distance'] / 1000;
				$this->travelTime += $route['duration'];
			}
		}
		$this->geoJson = [
			'type' => 'LineString',
			'coordinates' => $coordinates,
		];
	}

	/** {@inheritdoc} */
	public function parsePoints(): array
	{
		$tracks = [
			$this->start['lon'] . ',' . $this->start['lat'],
		];
		if (!empty($this->indirectPoints)) {
			foreach ($this->indirectPoints as $tempLon) {
				$tracks[] = $tempLon['lon'] . ',' . $tempLon['lat'];
			}
		}
		$tracks[] = $this->end['lon'] . ',' . $this->end['lat'];
		return $tracks;
	}
}
