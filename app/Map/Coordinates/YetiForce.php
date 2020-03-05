<?php

/**
 * YetiForce driver file to get coordinates.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Map\Coordinates;

/**
 * YetiForce driver class to get coordinates.
 */
class YetiForce extends Base
{
	/**
	 * {@inheritdoc}
	 */
	public function getCoordinates(array $addressInfo)
	{
		$product = \App\YetiForce\Register::getProducts('YetiForceMap');
		if (empty($addressInfo) || !\App\RequestUtil::isNetConnection() || empty($product['params']['login']) || empty($product['params']['pass'])) {
			return false;
		}
		$params = array_merge([
			'format' => 'json',
			'addressdetails' => 1,
			'limit' => 1,
			'accept-language' => \App\Language::getLanguage() . ',' . \App\Config::main('default_language') . ',en-US',
		], $addressInfo);
		$coordinates = false;
		try {
			$response = (new \GuzzleHttp\Client(\App\RequestHttp::getOptions()))
				->request('GET', 'https://osm-search.yetiforce.eu/?' . \http_build_query($params), [
					'auth' => [$product['params']['login'], $product['params']['pass']],  'headers' => ['InsKey' => \App\YetiForce\Register::getInstanceKey()]
				]);
			if (200 === $response->getStatusCode()) {
				$coordinates = \App\Json::decode($response->getBody());
			} else {
				\App\Log::error('Error with connection - ' . $response->getReasonPhrase(), __CLASS__);
			}
		} catch (\Exception $ex) {
			\App\Log::error('Error - ' . $ex->getMessage(), __CLASS__);
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
