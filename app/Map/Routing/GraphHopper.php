<?php
/**
 * Connector to find routing. Connector based on service GraphHopper.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 *
 * @see       https://graphhopper.com
 * @see       https://github.com/graphhopper/graphhopper
 */

namespace App\Map\Routing;

/**
 * Connector for service GraphHopper to get routing.
 */
class GraphHopper extends Base
{
	/** {@inheritdoc} */
	public function calculate()
	{
		if (!\App\RequestUtil::isNetConnection()) {
			throw new \App\Exceptions\AppException('ERR_NO_INTERNET_CONNECTION');
		}
		$options = [
			'timeout' => 120,
			'http_errors' => false,
			'json' => array_merge([
				'points' => $this->parsePoints(),
				'points_encoded' => false,
				'locale' => \App\Language::getShortLanguageName(),
			], $this->params),
		];
		$url = $this->url . '/route';
		\App\Log::beginProfile("POST|YetiForceRouting::calculate|{$url}", __NAMESPACE__);
		$response = (new \GuzzleHttp\Client(\App\RequestHttp::getOptions()))->post($url, $options);
		\App\Log::endProfile("POST|YetiForceRouting::calculate|{$url}", __NAMESPACE__);
		if (200 === $response->getStatusCode()) {
			$json = \App\Json::decode($response->getBody());
		} else {
			throw new \App\Exceptions\AppException('Error with connection |' . $response->getReasonPhrase() . '|' . $response->getBody(), 500);
		}
		$coordinates = [];
		$description = '';
		if (!empty($json['paths'])) {
			foreach ($json['paths'] as $path) {
				$coordinates = array_merge($coordinates, $path['points']['coordinates']);
				$this->distance += $path['distance'] / 1000;
				$this->travelTime += $path['time'] / 1000;
				foreach ($path['instructions'] as $instruction) {
					$description .= $instruction['text'] . ($instruction['distance'] ? ' (' . (int) $instruction['distance'] . 'm)' : '') . '<br>';
				}
			}
		}
		$this->geoJson = [
			'type' => 'LineString',
			'coordinates' => $coordinates,
		];
		$this->description = $description;
	}

	/** {@inheritdoc} */
	public function parsePoints(): array
	{
		$tracks = [
			[$this->start['lon'], $this->start['lat']],
		];
		if (!empty($this->indirectPoints)) {
			foreach ($this->indirectPoints as $tempLon) {
				$tracks[] = [$tempLon['lon'], $tempLon['lat']];
			}
		}
		$tracks[] = [$this->end['lon'], $this->end['lat']];
		return $tracks;
	}
}
