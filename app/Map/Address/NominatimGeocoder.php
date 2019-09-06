<?php

/**
 * Address finder nominatim geocoder file.
 *
 * @see       https://nominatim.org Documentation of Nominatim API
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Map\Address;

/**
 * Address finder nominatim geocoder class.
 */
class NominatimGeocoder extends Base
{
	/**
	 * {@inheritdoc}
	 */
	public $link = 'https://nominatim.org/release-docs/develop/';

	/**
	 * {@inheritdoc}
	 */
	public $customFields = [
		'country_codes' => [
			'label' => 'LBL_COUNTRY_CODES',
			'type' => 'text',
			'placeholder' => 'LBL_COUNTRY_CODES_PLACEHOLDER',
		],
		'map_url' => [
			'label' => 'LBL_PROVIDER_MAP_URL',
			'type' => 'text',
			'validator' => 'required'
		],
	];

	/**
	 * Function checks if provider is set.
	 *
	 * @return bool
	 */
	public function isSet()
	{
		$provider = \App\Map\Address::getConfig()[$this->getName()] ?? 0;
		return (bool) $provider ? $provider['map_url'] ?? 0 : 0;
	}

	/**
	 * {@inheritdoc}
	 */
	public function find($value): array
	{
		if (empty($value) || !\App\RequestUtil::isNetConnection()) {
			return [];
		}
		$params = [
			'format' => 'json',
			'addressdetails' => 1,
			'limit' => \App\Map\Address::getConfig()['global']['result_num'],
			'accept-language' => \App\Language::getLanguage() . ',' . \App\Config::main('default_language') . ',en-US',
			'q' => $value
		];
		if ($countryCode = \App\Map\Address::getConfig()[$this->getName()]['country_codes']) {
			$params['countrycodes'] = implode(',', $countryCode);
		}
		$options = [];
		if (!empty(\Config\Components\AddressFinder::$nominatimMapUrlCustomOptions)) {
			$options = \Config\Components\AddressFinder::$nominatimMapUrlCustomOptions;
		}
		$rows = [];
		try {
			$response = (new \GuzzleHttp\Client(\App\RequestHttp::getOptions()))
				->request('GET', \App\Map\Address::getConfig()[$this->getName()]['map_url'] . '/?' . \http_build_query($params), $options);
			if (200 !== $response->getStatusCode()) {
				throw new \App\Exceptions\AppException('Error with connection |' . $response->getStatusCode());
			}
			$body = $response->getBody();
			$body = \App\Json::isEmpty($body) ? [] : \App\Json::decode($body);
			if ($body) {
				$mainMapping = \Config\Components\AddressFinder::nominatimRemapping();
				if (!\is_callable($mainMapping)) {
					$mainMapping = [$this, 'parseRow'];
				}
				$countryMapping = \Config\Components\AddressFinder::nominatimRemappingForCountry();
				foreach ($body as $row) {
					$mappingFunction = $mainMapping;
					if (isset($row['address']['country_code'], $countryMapping[\strtoupper($row['address']['country_code'])])) {
						$mappingFunction = $countryMapping[\strtoupper($row['address']['country_code'])];
					}
					$rows[] = [
						'label' => $row['display_name'],
						'address' => \call_user_func_array($mappingFunction, [$row])
					];
				}
			}
		} catch (\Throwable $ex) {
			\App\Log::warning('Error - ' . __CLASS__ . ' - ' . $ex->getMessage());
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
	private function parseRow(array $row): array
	{
		return [
			'addresslevel1' => [$row['address']['country'] ?? '', strtoupper($row['address']['country_code'] ?? '')],
			'addresslevel2' => $row['address']['state'] ?? '',
			'addresslevel3' => $row['address']['state_district'] ?? '',
			'addresslevel4' => $row['address']['county'] ?? '',
			'addresslevel5' => $row['address']['city'] ?? $row['address']['town'] ?? $row['address']['village'] ?? '',
			'addresslevel6' => $row['address']['suburb'] ?? $row['address']['neighbourhood'] ?? $row['address']['city_district'] ?? '',
			'addresslevel7' => $row['address']['postcode'] ?? '',
			'addresslevel8' => $row['address']['road'] ?? '',
			'buildingnumber' => $row['address']['house_number'] ?? '',
			'localnumber' => $row['address']['local_number'] ?? '',
		];
	}
}
