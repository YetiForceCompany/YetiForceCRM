<?php

/**
 * Address finder OpenCageGeocoder file.
 *
 * @see       https://geocoder.opencagedata.com/api Documentation  of OpenCage Geocoder API
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Map\Address;

/**
 * Address finder OpenCageGeocoder class.
 */
class OpenCageGeocoder extends Base
{
	/**
	 * {@inheritdoc}
	 */
	public $customFields = [
		'country_codes' => [
			'type' => 'text',
			'info' => 'LBL_COUNTRY_CODES_INFO',
		],
		'key' => [
			'type' => 'text',
			'validator' => 'required,custom[onlyLetterNumber]'
		],
	];
	/**
	 * API Address to retrieve data.
	 *
	 * @var string
	 */
	protected static $url = 'https://api.opencagedata.com/geocode/v1/';

	/**
	 * {@inheritdoc}
	 */
	public $link = 'https://opencagedata.com/api/';

	/**
	 * {@inheritdoc}
	 */
	public function find($value)
	{
		if (empty($value) || !\App\RequestUtil::isNetConnection()) {
			return [];
		}
		$config = \App\Map\Address::getConfig();
		$urlAddress = static::$url . 'json?q=' . $value . '&pretty=1';
		$urlAddress .= '&language=' . \App\Language::getLanguage();
		$urlAddress .= '&limit=' . $config['global']['result_num'];
		$urlAddress .= '&key=' . $config['OpenCageGeocoder']['key'];
		if ($countryCode = \App\Map\Address::getConfig()[$this->getName()]['country_codes']) {
			$urlAddress .= '&countrycode=' . (strpos($countryCode, ',') ? implode(',', $countryCode) : $countryCode);
		}
		try {
			$response = \Requests::get($urlAddress);
			if (!$response->success) {
				\App\Log::warning($response->status_code . ' ' . $response->body, __NAMESPACE__);
				return false;
			}
			$body = \App\Json::decode($response->body);
			$rows = [];
			if ($body['total_results']) {
				$mainMapping = \App\Config::component('AddressFinder', 'REMAPPING_OPENCAGE');
				if (!\is_callable($mainMapping)) {
					$mainMapping = [$this, 'parseRow'];
				}
				$countryMapping = \App\Config::component('AddressFinder', 'REMAPPING_OPENCAGE_FOR_COUNTRY');
				foreach ($body['results'] as $row) {
					$mappingFunction = $mainMapping;
					if (isset($row['components']['country'], $countryMapping[$row['components']['country']])) {
						$mappingFunction = $countryMapping[$row['components']['country']];
					}
					$rows[] = [
						'label' => $row['formatted'],
						'address' => \call_user_func_array($mappingFunction, [$row])
					];
				}
			}
		} catch (\Throwable $e) {
			\App\Log::warning($e->getMessage());
			return false;
		}
		return $rows;
	}

	/**
	 * Main function to parse information about address.
	 *
	 * @param array $row
	 *
	 * @return array
	 */
	private function parseRow(array $row)
	{
		return [
			'addresslevel1' => [$row['components']['country'] ?? '', $row['components']['ISO_3166-1_alpha-2'] ?? ''],
			'addresslevel2' => $row['components']['state'] ?? '',
			'addresslevel3' => $row['components']['state_district'] ?? '',
			'addresslevel4' => $row['components']['county'] ?? '',
			'addresslevel5' => $row['components']['city'] ?? $row['components']['town'] ?? $row['components']['village'] ?? '',
			'addresslevel6' => $row['components']['suburb'] ?? $row['components']['neighbourhood'] ?? $row['components']['city_district'] ?? '',
			'addresslevel7' => $row['components']['postcode'] ?? '',
			'addresslevel8' => $row['components']['road'] ?? '',
			'buildingnumber' => $row['components']['house_number'] ?? '',
			'localnumber' => $row['components']['local_number'] ?? '',
		];
	}
}
