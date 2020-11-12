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
	/** @var string[] Supported languages. */
	protected $languages = ['de-DE', 'en-US', 'es-ES', 'fr-FR', 'gr-GR', 'hu-HU', 'id-ID', 'it-IT', 'ne-NP', 'nl-NL', 'pt-PT', 'ru-RU', 'zh-CN'];

	/**  {@inheritdoc} */
	public function calculate()
	{
		$product = \App\YetiForce\Register::getProducts('YetiForceMap');
		if (!\App\RequestUtil::isNetConnection() || ((empty($product['params']['login']) || empty($product['params']['pass'])) && empty($product['params']['token']))) {
			throw new \App\Exceptions\AppException('ERR_NO_INTERNET_CONNECTION');
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
		$options = [
			'timeout' => 60,
			'http_errors' => false,
			'headers' => [
				'Accept' => 'application/json, application/geo+json, application/gpx+xml, img/png; charset=utf-8',
				'InsKey' => \App\YetiForce\Register::getInstanceKey()
			]
		];
		if (isset($product['params']['token'])) {
			$params['yf_token'] = $product['params']['token'];
		} else {
			$options['auth'] = [$product['params']['login'], $product['params']['pass']];
		}
		$coordinates = [];
		$travel = $distance = 0;
		$description = '';

		$url = 'https://osm-route.yetiforce.eu/ors/directions?' . \http_build_query($params);
		\App\Log::beginProfile("GET|YetiForce::calculate|{$url}", __NAMESPACE__);
		$response = (new \GuzzleHttp\Client(\App\RequestHttp::getOptions()))->request('GET', $url, $options);
		\App\Log::endProfile("GET|YetiForce::calculate|{$url}", __NAMESPACE__);
		if (200 === $response->getStatusCode()) {
			$json = \App\Json::decode($response->getBody());
		} else {
			throw new \App\Exceptions\AppException('Error with connection |' . $response->getReasonPhrase() . '|' . $response->getBody(), 500);
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
	}

	/**  {@inheritdoc} */
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
