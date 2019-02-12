<?php
/**
 * Connector to find route. Connector based on service YOURS.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 *
 * @link      https://wiki.openstreetmap.org/wiki/YOURS
 */

namespace App\Map\Route;

/**
 * Connector for service YOURS to get route.
 */
class Yours extends Base
{
	/**
	 * {@inheritdoc}
	 */
	public function calculate()
	{
		if (!\App\RequestUtil::isNetConnection()) {
			return;
		}
		$startLat = $this->start['lat'];
		$startLon = $this->start['lon'];
		if (!empty($this->indirectPoints)) {
			foreach ($this->indirectPoints as $tempLon) {
				$endLon = $tempLon['lon'];
				$endLat = $tempLon['lat'];
				$tracks[] = [
					'startLat' => $startLat,
					'startLon' => $startLon,
					'endLat' => $endLat,
					'endLon' => $endLon,
				];
				$startLat = $endLat;
				$startLon = $endLon;
			}
		}
		$tracks[] = [
			'startLat' => $startLat,
			'startLon' => $startLon,
			'endLat' => $this->end['lat'],
			'endLon' => $this->end['lon']
		];
		$coordinates = [];
		$travel = $distance = 0;
		$description = '';
		$urlToRoute = \AppConfig::module('OpenStreetMap', 'ADDRESS_TO_ROUTE');
		try {
			foreach ($tracks as $track) {
				$url = $urlToRoute . '?format=geojson&flat=' . $track['startLat'] . '&flon=' . $track['startLon'] . '&tlat=' . $track['endLat'] . '&tlon=' . $track['endLon'] . '&lang=' . \App\Language::getLanguage() . '&instructions=1';
				$response = (new \GuzzleHttp\Client())->request('GET', $url, \App\RequestHttp::getOptions() + ['timeout' => 1]);
				if ($response->getStatusCode() === 200) {
					$json = \App\Json::decode($response->getBody());
				} else {
					\App\Log::warning('Error with connection - ' . __CLASS__);
					return;
				}
				$coordinates = array_merge($coordinates, $json['coordinates']);
				$description .= $json['properties']['description'];
				$travel = $travel + $json['properties']['traveltime'];
				$distance = $distance + $json['properties']['distance'];
			}
			$this->geoJson = [
				'type' => 'LineString',
				'coordinates' => $coordinates,
			];
			$this->travelTime = $travel;
			$this->distance = $distance;
			$this->description = $description;
		} catch (\Exception $ex) {
			\App\Log::warning($ex->getMessage());
		}
	}
}
