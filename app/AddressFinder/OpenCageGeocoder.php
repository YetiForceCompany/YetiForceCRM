<?php

namespace App\AddressFinder;

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
	/** String constant 'components' @var string */
	private const STR_COMPONENTS = 'components';
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
		return (bool) \App\AddressFinder::getConfig()['opencage_data']['nominatim'];
	}

	/**
	 * {@inheritdoc}
	 */
	public function find($value)
	{
		$config = \App\AddressFinder::getConfig();
		$urlStr = static::$url . 'json?q=' . $value . '&pretty=1';
		$urlStr .= '&language=' . \App\Language::getLanguageTag();
		$urlStr .= '&limit=' . $config['global']['result_num'];
		$urlStr .= '&key=' . $config['opencage_data']['key'];
		try {
			$response = \Requests::get($urlStr);
			if (!$response->success) {
				\App\Log::warning($response->status_code . ' ' . $response->body, __NAMESPACE__);
				return false;
			}
			$body = \App\Json::decode($response->body);
			$rows = [];
			if ($body['total_results']) {
				foreach ($body['results'] as $row) {
					$rows[] = [
						'label' => $row['formatted'],
						'address' => [
							'addresslevel1' => [$row[static::STR_COMPONENTS]['country'] ?? '', $row[static::STR_COMPONENTS]['ISO_3166-1_alpha-2'] ?? ''],
							'addresslevel2' => $row[static::STR_COMPONENTS]['state'] ?? '',
							'addresslevel3' => $row[static::STR_COMPONENTS]['state_district'] ?? '',
							'addresslevel4' => $row[static::STR_COMPONENTS]['county'] ?? '',
							'addresslevel5' => $row[static::STR_COMPONENTS]['city'] ?? $row[static::STR_COMPONENTS]['village'] ?? '',
							'addresslevel6' => $row[static::STR_COMPONENTS]['suburb'] ?? $row[static::STR_COMPONENTS]['neighbourhood'] ?? $row[static::STR_COMPONENTS]['city_district'] ?? '',
							'addresslevel7' => $row[static::STR_COMPONENTS]['postcode'] ?? '',
							'addresslevel8' => $row[static::STR_COMPONENTS]['road'] ?? '',
							'buildingnumber' => $row[static::STR_COMPONENTS]['house_number'] ?? '',
							'localnumber' => $row[static::STR_COMPONENTS]['local_number'] ?? '',
						],
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
