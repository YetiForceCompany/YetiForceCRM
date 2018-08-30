<?php

namespace App\AddressFinder;

/**
 * Address finder Google class.
 *
 * @see       maps.googleapis.com Documentation  of Google Geocoding API
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class GoogleGeocode extends Base
{
	/** String constant 'results' @var string */
	private const STR_RESULTS = 'results';
	/** String constant 'long_name' @var string */
	private const STR_LONG_NAME = 'long_name';
	/**
	 * API Address to retrieve data.
	 *
	 * @var string
	 */
	protected static $url = 'https://maps.googleapis.com/maps/api/geocode/json?';

	/**
	 * Function checks if teryt is active.
	 *
	 * @return bool
	 */
	public static function isActive()
	{
		return (bool) \App\AddressFinder::getConfig()['google_map_api']['nominatim'];
	}

	/**
	 * {@inheritdoc}
	 */
	public function find($value)
	{
		$key = \App\AddressFinder::getConfig()['google_map_api']['key'];
		$lang = \App\Language::getShortLanguageName();
		$response = \Requests::get(static::$url . "key={$key}&address=$value");
		if (!$response->success) {
			\App\Log::warning($response->status_code . ' ' . $response->body, __NAMESPACE__);
			return false;
		}
		$body = \App\Json::decode($response->body);
		if (isset($body[static::STR_RESULTS])) {
			$location = $body[static::STR_RESULTS][0]['geometry']['location'];
			$urlParam = "key={$key}&language={$lang}&latlng={$location['lat']},{$location['lng']}";
			$response = \Requests::get(static::$url . $urlParam);
			if (!$response->success) {
				\App\Log::warning($response->status_code . ' ' . $response->body, __NAMESPACE__);
				return false;
			}
			$body = \App\Json::decode($response->body);
			if (isset($body[static::STR_RESULTS])) {
				foreach ($body[static::STR_RESULTS] as $row) {
					$rows[] = [
						'label' => $row['formatted_address'],
						'address' => static::parse($row['address_components'])
					];
				}
			}
		}
		return $rows;
	}

	/**
	 * Parse response.
	 *
	 * @param array $rows
	 *
	 * @return string[]
	 */
	private function parse($rows)
	{
		$address = [];
		foreach ($rows as $row) {
			switch ($row['types'][0]) {
				case 'street_number':
					if (strpos($row[static::STR_LONG_NAME], '/') !== false) {
						list($address['buildingnumber'], $address['localnumber']) = explode('/', $row[static::STR_LONG_NAME], 2);
					} else {
						$address['buildingnumber'] = $row[static::STR_LONG_NAME];
					}
					break;
				case 'route':
					$address['addresslevel8'] = $row[static::STR_LONG_NAME];
					break;
				case 'postal_code':
					if (empty($row['types'][1])) {
						$address['addresslevel7'] = $row[static::STR_LONG_NAME];
					}
					break;
				case 'neighborhood':
					$address['addresslevel6'] = $row[static::STR_LONG_NAME];
					break;
				case 'sublocality':
					$address['addresslevel6'] = $row[static::STR_LONG_NAME];
					break;
				case 'locality':
					$address['addresslevel5'] = $row[static::STR_LONG_NAME];
					break;
				case 'administrative_area_level_3':
					$address['addresslevel4'] = $row[static::STR_LONG_NAME];
					break;
				case 'administrative_area_level_2':
					$address['addresslevel3'] = $row[static::STR_LONG_NAME];
					break;
				case 'administrative_area_level_1':
					$address['addresslevel2'] = $row[static::STR_LONG_NAME];
					break;
				case 'country':
					$address['addresslevel1'] = $row[static::STR_LONG_NAME];
					break;
				default:
					break;
			}
		}
		return $address;
	}
}
