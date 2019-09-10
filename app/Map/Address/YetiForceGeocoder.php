<?php

/**
 * Address finder YetiForce geocoder file.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Map\Address;

/**
 * Address finder YetiForce geocoder class.
 */
class YetiForceGeocoder extends Base
{
	/**
	 * {@inheritdoc}
	 */
	public $link = 'index.php?module=YetiForce&parent=Settings&view=Shop&product=YetiForceGeocoder&mode=showProductModal';

	/**
	 * {@inheritdoc}
	 */
	public $customFields = [
		'country_codes' => [
			'type' => 'text',
			'info' => 'LBL_COUNTRY_CODES_INFO',
		]
	];

	/**
	 * {@inheritdoc}
	 */
	public function isSet()
	{
		return \App\YetiForce\Shop::check('YetiForceGeocoder');
	}

	/**
	 * {@inheritdoc}
	 */
	public function find($value): array
	{
		$product = \App\YetiForce\Register::getProducts('YetiForceGeocoder');
		if (empty($value) || !\App\RequestUtil::isNetConnection() || empty($product['params']['login']) || empty($product['params']['pass'])) {
			return [];
		}
		$params = [
			'format' => 'json',
			'addressdetails' => 1,
			'limit' => \App\Map\Address::getConfig()['global']['result_num'],
			'accept-language' => \App\Language::getLanguage() . ',' . \App\Config::main('default_language') . ',en-US',
			'q' => $value
		];
		if ($countryCodes = \App\Map\Address::getConfig()[$this->getName()]['country_codes']) {
			$params['countrycodes'] = $countryCodes;
		}
		$rows = [];
		try {
			$response = (new \GuzzleHttp\Client(\array_merge(\App\RequestHttp::getOptions(), ['InsKey' => \App\YetiForce\Register::getInstanceKey()])))
				->request('GET', 'https://openstreetmap.yetiforce.eu/?' . \http_build_query($params), [
					'auth' => [$product['params']['login'], $product['params']['pass']]
				]);
			if (200 !== $response->getStatusCode()) {
				throw new \App\Exceptions\AppException('Error with connection |' . $response->getStatusCode());
			}
			$body = $response->getBody();
			$body = \App\Json::isEmpty($body) ? [] : \App\Json::decode($body);
			if ($body) {
				$mainMapping = \Config\Components\AddressFinder::yetiForceRemapping();
				if (!\is_callable($mainMapping)) {
					$mainMapping = [$this, 'parseRow'];
				}
				$countryMapping = \Config\Components\AddressFinder::yetiForceRemappingForCountry();
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
