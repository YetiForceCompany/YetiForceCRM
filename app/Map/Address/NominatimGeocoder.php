<?php

/**
 * Address finder nominatim geocoder file.
 *
 * @see       https://nominatim.org Documentation of Nominatim API
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\Map\Address;

/**
 * Address finder nominatim geocoder class.
 */
class NominatimGeocoder extends Base
{
	/** {@inheritdoc} */
	public $docUrl = 'https://nominatim.org/release-docs/develop/';

	/** {@inheritdoc} */
	public $customFields = [
		'country_codes' => [
			'uitype' => 1,
			'label' => 'LBL_COUNTRY_CODES',
			'purifyType' => \App\Purifier::TEXT,
			'maximumlength' => '100',
			'typeofdata' => 'V~O',
			'tooltip' => 'LBL_COUNTRY_CODES_PLACEHOLDER',
			'link' => [
				'title' => 'LBL_COUNTRY_CODES_INFO',
				'url' => 'https://wikipedia.org/wiki/List_of_ISO_3166_country_codes',
			]
		],
		'map_url' => [
			'uitype' => 17,
			'label' => 'LBL_MAP_URL',
			'purifyType' => \App\Purifier::URL,
			'maximumlength' => '200',
			'typeofdata' => 'V~M'
		],
	];

	/** {@inheritdoc} */
	public function isConfigured()
	{
		return !empty($this->config['map_url']);
	}

	/** {@inheritdoc} */
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
			'q' => $value,
		];
		if (!empty($this->config['country_codes'])) {
			$params['countrycodes'] = $this->config['country_codes'];
		}
		$options = [];
		if (!empty(\Config\Components\AddressFinder::$nominatimMapUrlCustomOptions)) {
			$options = \Config\Components\AddressFinder::$nominatimMapUrlCustomOptions;
		}
		$rows = [];
		try {
			$url = $this->config['map_url'] . '/?' . \http_build_query($params);
			\App\Log::beginProfile("GET|NominatimGeocoder::find|{$url}", __NAMESPACE__);
			$response = (new \GuzzleHttp\Client(\App\RequestHttp::getOptions()))
				->request('GET', $url, $options);
			\App\Log::endProfile("GET|NominatimGeocoder::find|{$url}", __NAMESPACE__);
			if (200 !== $response->getStatusCode()) {
				throw new \App\Exceptions\AppException('Error with connection |' . $response->getReasonPhrase() . '|' . $response->getBody());
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
						'address' => \call_user_func_array($mappingFunction, [$row]),
						'coordinates' => ['lat' => $row['lat'], 'lon' => $row['lon']],
						'countryCode' => $row['address']['country_code'] ?? '',
					];
				}
			}
		} catch (\Throwable $ex) {
			\App\Log::error('Error - ' . $ex->getMessage(), __CLASS__);
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
			'addresslevel3' => $row['address']['county'] ?? $row['address']['state_district'] ?? '',
			'addresslevel4' => $row['address']['municipality'] ?? '',
			'addresslevel5' => $row['address']['city'] ?? $row['address']['town'] ?? $row['address']['village'] ?? '',
			'addresslevel6' => $row['address']['hamlet'] ?? $row['address']['suburb'] ?? $row['address']['neighbourhood'] ?? $row['address']['city_district'] ?? '',
			'addresslevel7' => $row['address']['postcode'] ?? '',
			'addresslevel8' => $row['address']['road'] ?? '',
			'buildingnumber' => $row['address']['house_number'] ?? '',
			'localnumber' => $row['address']['local_number'] ?? '',
			'company_name_' => $row['address']['office'] ?? '',
		];
	}
}
