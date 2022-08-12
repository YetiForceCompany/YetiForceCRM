<?php
/**
 * NorthData API file.
 *
 * @see https://www.northdata.com/
 * @see https://www.northdata.com/_coverage
 * @see https://github.com/northdata/api
 * @see https://northdata.github.io/doc/api/
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Rembiesa <s.rembiesa@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\RecordCollectors;

/**
 * NorthData API class.
 */
class NorthData extends Base
{
	/** {@inheritdoc} */
	public $allowedModules = ['Accounts', 'Leads', 'Vendors', 'Partners', 'Competition'];

	/** {@inheritdoc} */
	public $icon = 'yfi-north-data';

	/** {@inheritdoc} */
	public $label = 'LBL_NORTH_DATA';

	/** {@inheritdoc} */
	public $displayType = 'FillFields';

	/** {@inheritdoc} */
	public $description = 'LBL_NORTH_DATA_DESC';

	/** {@inheritdoc} */
	public $docUrl = 'https://www.northdata.com/_data';

	/** @var string NorthData sever address */
	protected $url = 'https://www.northdata.com/_api/';

	/** {@inheritdoc} */
	public $settingsFields = [
		'api_key' => ['required' => 1, 'purifyType' => 'Text', 'label' => 'LBL_API_KEY']
	];

	/** @var string Api Key. */
	private $apiKey;

	/** {@inheritdoc} */
	protected $fields = [
		'companyName' => [
			'labelModule' => '_Base',
			'label' => 'Account Name',
		],
		'country' => [
			'labelModule' => '_Base',
			'label' => 'Country',
			'picklistModule' => 'Other.Country',
			'uitype' => 16,
			'picklistValues' => [
				'AT' => 'Austria',
				'FR' => 'France',
				'DE' => 'Germany',
				'LU' => 'Luxembourg',
				'PL' => 'Poland',
				'ES' => 'Spain',
				'CH' => 'Switzerland',
				'GB' => 'United Kingdom',
			]
		],
	];

	/** {@inheritdoc} */
	protected $modulesFieldsMap = [
		'Accounts' => [
			'companyName' => 'accountname',
		],
		'Leads' => [
			'companyName' => 'company',
		],
		'Partners' => [
			'companyName' => 'subject',
		],
		'Vendors' => [
			'companyName' => 'vendorname',
		],
		'Competition' => [
			'companyName' => 'subject',
		],
	];

	/** {@inheritdoc} */
	public $formFieldsToRecordMap = [
		'Accounts' => [
			'nameName' => 'accountname',
			'registerId' => 'registration_number_1',
			'capitalItems0Value' => 'annual_revenue',
			'segmentCodesUksic' => 'siccode',
			'addressStreet' => 'addresslevel8a',
			'addressPostalCode' => 'addresslevel7a',
			'addressCity' => 'addresslevel5a',
			'addressState' => 'addresslevel2a',
			'addressCountry' => 'addresslevel1a',
		],
		'Leads' => [
			'nameName' => 'company',
			'registerId' => 'registration_number_1',
			'addressStreet' => 'addresslevel8a',
			'addressPostalCode' => 'addresslevel7a',
			'addressCity' => 'addresslevel5a',
			'addressState' => 'addresslevel2a',
			'addressCountry' => 'addresslevel1a',
		],
		'Partners' => [
			'nameName' => 'subject',
			'addressStreet' => 'addresslevel8a',
			'addressPostalCode' => 'addresslevel7a',
			'addressCity' => 'addresslevel5a',
			'addressState' => 'addresslevel2a',
			'addressCountry' => 'addresslevel1a',
		],
		'Vendors' => [
			'nameName' => 'vendorname',
			'registerId' => 'registration_number_1',
			'addressStreet' => 'addresslevel8a',
			'addressPostalCode' => 'addresslevel7a',
			'addressCity' => 'addresslevel5a',
			'addressState' => 'addresslevel2a',
			'addressCountry' => 'addresslevel1a',
		],
		'Competition' => [
			'nameName' => 'subject',
			'addressStreet' => 'addresslevel8a',
			'addressPostalCode' => 'addresslevel7a',
			'addressCity' => 'addresslevel5a',
			'addressState' => 'addresslevel2a',
			'addressCountry' => 'addresslevel1a',
		],
	];

	/** {@inheritdoc} */
	public function isActive(): bool
	{
		return parent::isActive() && ($params = $this->getParams()) && !empty($params['api_key']);
	}

	/** {@inheritdoc} */
	public function search(): array
	{
		if (!$this->isActive()) {
			return [];
		}
		$params = [];
		if ($companyName = $this->request->getByType('companyName', 'Text')) {
			$params['query'] = $companyName;
		}
		if ($country = $this->request->getByType('country', 'Text')) {
			$params['countries'] = $country;
		}
		if (empty($params)) {
			return [];
		}
		$this->setApiKey();
		$this->getDataFromApi($params);
		$this->loadData();
		return $this->response;
	}

	/**
	 * Function fetching company data by params.
	 *
	 * @param array $query
	 * @param array $params
	 *
	 * @return void
	 */
	private function getDataFromApi(array $params): void
	{
		$language = 'de' === \App\Language::getShortLanguageName() ? 'de' : 'en';
		$params['language'] = $language;
		$params['limit'] = 4;
		try {
			$client = \App\RequestHttp::getClient(['headers' => ['X-Api-Key' => $this->apiKey]]);
			$suggestResponse = $client->get($this->url . 'search/v1/suggest?' . http_build_query($params));
			if (200 === $suggestResponse->getStatusCode()) {
				$suggestResponse = \App\Json::decode($suggestResponse->getBody()->getContents());
				foreach ($suggestResponse['results'] as $key => $company) {
					$companyResponse = $client->get($this->url . 'company/v1/company?' . http_build_query([
						'companyId' => $company['company']['id'],
						'financials' => true, 'relations' => true, 'sheets' => true, 'extras' => true,
						'language' => $language,
					]));
					$companyResponse = \App\Json::decode($companyResponse->getBody()->getContents());
					$this->response['links'][$key] = $companyResponse['northDataUrl'];
					$this->data[$key] = $this->parseData($companyResponse);
				}
			}
		} catch (\GuzzleHttp\Exception\GuzzleException $e) {
			\App\Log::warning($e->getMessage(), 'RecordCollectors');
			$this->response['error'] = $e->getResponse()->getReasonPhrase();
		}
	}

	/**
	 * Function parsing data to fields from ORB Intelligence API.
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	private function parseData(array $data): array
	{
		unset($data['northDataUrl'],$data['id']);
		$data['address']['country'] = \App\Fields\Country::getCountryName($data['address']['country']);
		if (isset($data['segmentCodes']['isic'])) {
			$data['segmentCodes']['isic'] = implode(', ', $data['segmentCodes']['isic']);
		}
		if (isset($data['segmentCodes']['naics'])) {
			$data['segmentCodes']['naics'] = implode(', ', $data['segmentCodes']['naics']);
		}
		if (isset($data['segmentCodes']['nace'])) {
			$data['segmentCodes']['nace'] = implode(', ', $data['segmentCodes']['nace']);
		}
		if (isset($data['segmentCodes']['wz'])) {
			$data['segmentCodes']['wz'] = implode(', ', $data['segmentCodes']['wz']);
		}
		return \App\Utils::flattenKeys($data, 'ucfirst');
	}

	/**
	 * Function setup Api Key.
	 *
	 * @return void
	 */
	private function setApiKey(): void
	{
		if (($params = $this->getParams()) && !empty($params['api_key'])) {
			$this->apiKey = $params['api_key'];
		} else {
			throw new \App\Exceptions\IllegalValue('You must fist setup Api Key in Config Panel', 403);
		}
	}
}
