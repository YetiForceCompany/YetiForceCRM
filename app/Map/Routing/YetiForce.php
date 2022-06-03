<?php
/**
 * Connector to find routing. Connector based on service YetiForce.
 *
 * The file is part of the paid functionality. Using the file is allowed only after purchasing a subscription. File modification allowed only with the consent of the system producer.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Map\Routing;

/**
 * Connector for service YetiForce to get routing.
 */
class YetiForce extends Base
{
	/** {@inheritdoc} */
	public function calculate()
	{
		$product = \App\YetiForce\Register::getProducts('YetiForceMap');
		if (!\App\RequestUtil::isNetConnection() || ((empty($product['params']['login']) || empty($product['params']['pass'])) && empty($product['params']['token']))) {
			throw new \App\Exceptions\AppException('ERR_NO_INTERNET_CONNECTION');
		}
		$options = [
			'version' => 2.0,
			'timeout' => 120,
			'http_errors' => false,
			'headers' => [
				'InsKey' => \App\YetiForce\Register::getInstanceKey(),
			],
			'json' => array_merge([
				'points' => $this->parsePoints(),
				'points_encoded' => false,
				'locale' => \App\Language::getShortLanguageName(),
			], $this->params),
		];
		$params = [];
		if (isset($product['params']['token'])) {
			$params['yf_token'] = $product['params']['token'];
		} else {
			$options['auth'] = [$product['params']['login'], $product['params']['pass']];
		}
		$url = 'https://osm-route.yetiforce.eu?' . \http_build_query($params);
		\App\Log::beginProfile("POST|YetiForceRouting::calculate|{$url}", __NAMESPACE__);
		$response = (new \GuzzleHttp\Client(\App\RequestHttp::getOptions()))->post($url, $options);
		\App\Log::endProfile("POST|YetiForceRouting::calculate|{$url}", __NAMESPACE__);
		if (200 === $response->getStatusCode()) {
			$json = \App\Json::decode($response->getBody());
		} else {
			$error = 'Error with connection |' . $response->getReasonPhrase() . '|' . $response->getBody();
			$json = \App\Json::decode($response->getBody());
			if (400 === $response->getStatusCode() && isset($json['message'])) {
				$error = $json['message'];
			}
			throw new \App\Exceptions\AppException($error, 500);
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
