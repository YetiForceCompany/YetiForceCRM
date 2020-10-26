<?php
/**
 * Connector to find routing. Connector based on service YetiForce.
 *
 * The file is part of the paid functionality. Using the file is allowed only after purchasing a subscription. File modification allowed only with the consent of the system producer.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Map\Routing;

/**
 * Connector for service YetiForce to get routing.
 */
class YetiForce extends Base
{
	/**
	 * Supported languages.
	 *
	 * @var float[]
	 */
	protected $languages = ['de-DE', 'en-US', 'es-ES', 'fr-FR', 'gr-GR', 'hu-HU', 'id-ID', 'it-IT', 'ne-NP', 'nl-NL', 'pt-PT', 'ru-RU', 'zh-CN'];

	/**
	 * {@inheritdoc}
	 */
	public function calculate()
	{
		if (!\App\RequestUtil::isNetConnection()) {
			return;
		}
		$params = array_merge([
			'coordinates' => implode('|', $this->parsePoints()),
			'format' => 'geojson',
			'preference' => 'fastest',
			'profile' => 'driving-car',
			'units' => 'km',
			'language' => \in_array(\App\Language::getLanguage(), $this->languages) ? \App\Language::getLanguage() : 'en-US',
			'instructions_format' => 'html',
		], $this->params);
		$coordinates = [];
		$travel = $distance = 0;
		$description = '';
		try {
			$url = 'https://osm-route.yetiforce.eu/ors/directions?' . \http_build_query($params);
			\App\Log::beginProfile("GET|YetiForce::calculate|{$url}", __NAMESPACE__);
			$response = (new \GuzzleHttp\Client(\App\RequestHttp::getOptions()))->request('GET', $url,
			 ['timeout' => 5,  'auth' => ['yeti', 'CLVoEHh0Se'], 'http_errors' => false, 'headers' => [
			 	'Accept' => 'application/json, application/geo+json, application/gpx+xml, img/png; charset=utf-8',
			 ]]
			);
			\App\Log::endProfile("GET|YetiForce::calculate|{$url}", __NAMESPACE__);
			if (200 === $response->getStatusCode()) {
				$json = \App\Json::decode($response->getBody());
			} else {
				throw new \App\Exceptions\AppException('Error with connection |' . $response->getReasonPhrase() . '|' . $response->getBody());
			}
			foreach ($json['features'] as $feature) {
				$coordinates = array_merge($coordinates, $feature['geometry']['coordinates']);
				foreach ($feature['properties']['summary'] as $summary) {
					$distance += $summary['distance'];
					$travel += $summary['duration'];
				}
				foreach ($feature['properties']['segments'] as $segments) {
					foreach ($segments['steps'] as $steps) {
						$description .= $steps['instruction'] . '<br>';
					}
				}
			}
			$this->geoJson = [
				'type' => 'LineString',
				'coordinates' => $coordinates,
			];
			$this->travelTime = $travel;
			$this->distance = $distance;
			$this->description = $description;
		} catch (\Exception $ex) {
			\App\Log::error('Error - ' . $ex->getMessage(), __CLASS__);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function parsePoints(): array
	{
		$tracks = [
			$this->start['lon'] . ',' . $this->start['lat']
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
