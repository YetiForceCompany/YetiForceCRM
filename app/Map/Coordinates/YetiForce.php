<?php

/**
 * YetiForce driver file to get coordinates.
 *
 * The file is part of the paid functionality. Using the file is allowed only after purchasing a subscription. File modification allowed only with the consent of the system producer.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Map\Coordinates;

/**
 * YetiForce driver class to get coordinates.
 */
class YetiForce extends Base
{
	/** {@inheritdoc} */
	public function getCoordinates(array $addressInfo)
	{
		$product = \App\YetiForce\Register::getProducts('YetiForceMap');
		if (empty($addressInfo) || !\App\RequestUtil::isNetConnection() || ((empty($product['params']['login']) || empty($product['params']['pass'])) && empty($product['params']['token']))) {
			return false;
		}
		$params = array_merge([
			'version' => 2.0,
			'format' => 'json',
			'addressdetails' => 1,
			'limit' => 1,
			'accept-language' => \App\Language::getLanguage() . ',' . \App\Config::main('default_language') . ',en-US',
		], $addressInfo);
		$options = [
			'timeout' => 60,
			'headers' => ['InsKey' => \App\YetiForce\Register::getInstanceKey()],
		];
		if (isset($product['params']['token'])) {
			$params['yf_token'] = $product['params']['token'];
		} else {
			$options['auth'] = [$product['params']['login'], $product['params']['pass']];
		}
		$coordinates = false;
		try {
			$url = 'https://osm-search.yetiforce.eu/?' . \http_build_query($params);
			\App\Log::beginProfile("GET|YetiForce::getCoordinates|{$url}", __NAMESPACE__);
			$response = (new \GuzzleHttp\Client(\App\RequestHttp::getOptions()))->request('GET', $url, $options);
			\App\Log::endProfile("GET|YetiForce::getCoordinates|{$url}", __NAMESPACE__);
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
