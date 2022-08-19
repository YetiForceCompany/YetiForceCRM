<?php

/**
 * Address finder YetiForce geocoder file.
 *
 * The file is part of the paid functionality. Using the file is allowed only after purchasing a subscription. File modification allowed only with the consent of the system producer.
 *
 * @see       https://yetiforce.com/en/yetiforce-map-en
 * @see       https://yetiforce.com/en/yetiforce-address-search-en
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
 * Address finder YetiForce geocoder class.
 */
class YetiForceGeocoder extends Base
{
	/** {@inheritdoc} */
	public $docUrl = 'index.php?module=YetiForce&parent=Settings&view=Shop&product=YetiForceGeocoder&mode=showProductModal';

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
	];

	/** {@inheritdoc} */
	public function isActive()
	{
		return (bool) ($this->config['active'] ?? 0) && \App\YetiForce\Shop::check('YetiForceGeocoder');
	}

	/** {@inheritdoc} */
	public function isConfigured()
	{
		return \App\YetiForce\Shop::check('YetiForceGeocoder');
	}

	/** {@inheritdoc} */
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
			'q' => $value,
		];
		if (!empty($this->config['country_codes'])) {
			$params['countrycodes'] = $this->config['country_codes'];
		}
		$options = [
			'version' => 2.0,
			'timeout' => 30,
			'headers' => [
				'InsKey' => \App\YetiForce\Register::getInstanceKey(),
			],
		];
		if (isset($product['params']['token'])) {
			$params['yf_token'] = $product['params']['token'];
		} else {
			$options['auth'] = [$product['params']['login'], $product['params']['pass']];
		}
		$rows = [];
		try {
			$url = 'https://osm-search.yetiforce.eu/?' . \http_build_query($params);
			\App\Log::beginProfile("GET|YetiForceGeocoder::find|{$url}", __NAMESPACE__);
			$response = (new \GuzzleHttp\Client(\App\RequestHttp::getOptions()))->request('GET', $url, $options);
			\App\Log::endProfile("GET|YetiForceGeocoder::find|{$url}", __NAMESPACE__);
			if (200 !== $response->getStatusCode()) {
				throw new \App\Exceptions\AppException('Error with connection |' . $response->getReasonPhrase() . '|' . $response->getBody());
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
