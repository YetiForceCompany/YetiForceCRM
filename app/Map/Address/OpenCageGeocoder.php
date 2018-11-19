<?php

namespace App\Map\Address;

/**
 * Address finder OpenCageGeocoder class.
 *
 * @see       https://geocoder.opencagedata.com/api Documentation  of OpenCage Geocoder API
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OpenCageGeocoder extends Base
{
	/**
	 * API Address to retrieve data.
	 *
	 * @var string
	 */
	protected static $url = 'https://api.opencagedata.com/geocode/v1/';

	/**
	 * Function checks if teryt is active.
	 *
	 * @return bool
	 */
	public static function isActive()
	{
		return (bool) \App\Map\Address::getConfig()['opencage_data']['nominatim'];
	}

	/**
	 * {@inheritdoc}
	 */
	public function find($value)
	{
		$config = \App\Map\Address::getConfig();
		$urlAddress = static::$url . 'json?q=' . $value . '&pretty=1';
		$urlAddress .= '&language=' . \App\Language::getLanguageTag();
		$urlAddress .= '&limit=' . $config['global']['result_num'];
		$urlAddress .= '&key=' . $config['opencage_data']['key'];
		try {
			$response = \Requests::get($urlAddress);
			if (!$response->success) {
				\App\Log::warning($response->status_code . ' ' . $response->body, __NAMESPACE__);
				return false;
			}
			$body = \App\Json::decode($response->body);
			$rows = [];
			if ($body['total_results']) {
				$mainMapping = \AppConfig::module('AddressFinder', 'REMAPPING_OPENCAGE');
				$countryMapping = \AppConfig::module('AddressFinder', 'REMAPPING_OPENCAGE_FOR_COUNTRY');
				foreach ($body['results'] as $row) {
					$mappingFunction = $mainMapping;
					if (isset($row['components']['country'], $countryMapping[$row['components']['country']])) {
						$mappingFunction = $countryMapping[$row['components']['country']];
					}
					$rows[] = [
						'label' => $row['formatted'],
						'address' => call_user_func_array($mappingFunction, [$row])
					];
				}
			}
		} catch (\Throwable $e) {
			\App\Log::warning($e->getMessage());
			return false;
		}
		return $rows;
	}
}
