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
	/**
	 * API Address to retrieve data.
	 *
	 * @var string
	 */
	private static $url = 'https://api.opencagedata.com/geocode/v1/';

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
		try {
			$config = \App\AddressFinder::getConfig();
			$response = \Requests::post(static::$url, [], [
					'format' => 'json',
					'key' => $config['opencage_data']['key'],
					'q' => $value,
					'pretty' => 1,
					'language' => \App\Language::getLanguageInIetf(),
					'limit' => $config['global']['result_num']
			]);
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
							'addresslevel1' => [$row['components']['country'] ?? '', $row['components']['ISO_3166-1_alpha-2'] ?? ''],
							'addresslevel2' => $row['components']['state'] ?? '',
							'addresslevel3' => $row['components']['state_district'] ?? '',
							'addresslevel4' => $row['components']['county'] ?? '',
							'addresslevel5' => $row['components']['city'] ?? $row['components']['village'] ?? '',
							'addresslevel6' => $row['components']['suburb'] ?? $row['components']['neighbourhood'] ?? $row['components']['city_district'] ?? '',
							'addresslevel7' => $row['components']['postcode'] ?? '',
							'addresslevel8' => $row['components']['road'] ?? '',
							'buildingnumber' => $row['components']['house_number'] ?? '',
							'localnumber' => $row['components']['local_number'] ?? '',
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
