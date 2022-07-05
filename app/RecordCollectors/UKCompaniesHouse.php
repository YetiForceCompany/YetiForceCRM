<?php
/**
 * United Kingdom Companies House record collector file.
 *
 * @package App
 *
 * @see https://developer.company-information.service.gov.uk/
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Rembiesa <s.rembiesa@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\RecordCollectors;

/**
 * United Kingdom Companies House record collector class.
 */
class UKCompaniesHouse extends Base
{
	/** {@inheritdoc} */
	protected static $allowedModules = ['Accounts', 'Leads', 'Vendors', 'Competition'];

	/** {@inheritdoc} */
	public $icon = 'fas fa-house-signal';

	/** {@inheritdoc} */
	public $label = 'LBL_UNITED_KINGDOM_CH';

	/** {@inheritdoc} */
	public $displayType = 'FillFields';

	/** {@inheritdoc} */
	public $description = 'LBL_UNITED_KINGDOM_CH_DESC';

	/** {@inheritdoc} */
	public $docUrl = 'https://developer.company-information.service.gov.uk/';

	/** {@inheritdoc} */
	protected $fields = [
		'ncr' => [
			'labelModule' => '_Base',
			'label' => 'Registration number 1',
			'typeofdata' => 'V~O',
		],
		'companyName' => [
			'labelModule' => '_Base',
			'label' => 'Account name',
			'typeofdata' => 'V~O',
		]
	];

	/** {@inheritdoc} */
	public $modulesFieldsMap = [
		'Accounts' => [
			'ncr' => 'registration_number_1',
			'companyName' => 'accountname'
		],
		'Leads' => [
			'ncr' => 'registration_number_1',
			'companyName' => 'company'
		],
		'Vendors' => [
			'ncr' => 'registration_number_1',
			'companyName' => 'vendorname'
		],
		'Competition' => [
			'ncr' => 'registration_number_1',
			'companyName' => 'subject'
		]
	];

	/** {@inheritdoc} */
	public $formFieldsToRecordMap = [
		'Accounts' => [
			'company_name' => 'accountname',
			'company_number' => 'registration_number_1',
			'sic_codes0' => 'siccode',
			'registered_office_addressAddress_line_1' => 'addresslevel8a',
			'registered_office_addressAddress_line_2' => 'addresslevel3a',
			'registered_office_addressLocality' => 'addresslevel5a',
			'registered_office_addressPostal_code' => 'addresslevel7a',
			'registered_office_addressCountry' => 'addresslevel1a',
			'registered_office_addressRegion' => 'addresslevel4a',
			'registered_office_addressPo_box' => 'poboxa',
			'service_addressAddress_line_1' => 'addresslevel8c',
			'service_addressAddress_line_2' => 'addresslevel3c',
			'service_addressLocality' => 'addresslevel5b',
			'service_addressPostal_code' => 'addresslevel7c',
			'service_addressCountry' => 'addresslevel1c',
			'service_addressRegion' => 'addresslevel4c',
			'service_addressPo_box' => 'poboxb',
		],
		'Leads' => [
			'company_name' => 'company',
			'company_number' => 'registration_number_1',
			'registered_office_addressAddress_line_1' => 'addresslevel8a',
			'registered_office_addressAddress_line_2' => 'addresslevel3a',
			'registered_office_addressLocality' => 'addresslevel5a',
			'registered_office_addressPostal_code' => 'addresslevel7a',
			'registered_office_addressCountry' => 'addresslevel1a',
			'registered_office_addressRegion' => 'addresslevel4a',
			'registered_office_addressPo_box' => 'poboxa',
		],
		'Vendors' => [
			'company_name' => 'vendorname',
			'company_number' => 'registration_number_1',
			'registered_office_addressAddress_line_1' => 'addresslevel8a',
			'registered_office_addressAddress_line_2' => 'addresslevel3a',
			'registered_office_addressLocality' => 'addresslevel5a',
			'registered_office_addressPostal_code' => 'addresslevel7a',
			'registered_office_addressCountry' => 'addresslevel1a',
			'registered_office_addressRegion' => 'addresslevel4a',
			'registered_office_addressPo_box' => 'poboxa',
			'service_addressAddress_line_1' => 'addresslevel8c',
			'service_addressAddress_line_2' => 'addresslevel3c',
			'service_addressLocality' => 'addresslevel5b',
			'service_addressPostal_code' => 'addresslevel7c',
			'service_addressCountry' => 'addresslevel1c',
			'service_addressRegion' => 'addresslevel4c',
			'service_addressPo_box' => 'poboxb',
		],
		'Competition' => [
			'company_name' => 'subject',
			'registered_office_addressAddress_line_1' => 'addresslevel8a',
			'registered_office_addressAddress_line_2' => 'addresslevel3a',
			'registered_office_addressLocality' => 'addresslevel5a',
			'registered_office_addressPostal_code' => 'addresslevel7a',
			'registered_office_addressCountry' => 'addresslevel1a',
			'registered_office_addressRegion' => 'addresslevel4a',
			'registered_office_addressPo_box' => 'poboxa',
		]
	];

	/** @var array Configuration field list. */
	public $settingsFields = [
		'api_key' => ['required' => 1, 'purifyType' => 'Text', 'label' => 'LBL_API_KEY'],
	];

	/** @var string Api Key. */
	private $apiKey;

	/** @var string CH sever address */
	private $url = 'https://api.company-information.service.gov.uk/';

	/** @var int Limit for fetching companies */
	const LIMIT = 4;

	/** {@inheritdoc} */
	public function isActive(): bool
	{
		return parent::isActive() && ($params = $this->getParams()) && !empty($params['api_key']);
	}

	/** {@inheritdoc} */
	public function search(): array
	{
		$this->setApiKey();
		$this->moduleName = $this->request->getModule();
		$ncr = str_replace([' ', ',', '.', '-'], '', $this->request->getByType('ncr', 'Text'));
		$companyName = str_replace([',', '.', '-'], ' ', $this->request->getByType('companyName', 'Text'));

		if ($ncr) {
			$this->data = $this->getDataFromApiByNcr($ncr);
			if (empty($this->data)) {
				return [];
			}
		} elseif ($companyName) {
			$this->getDataFromApiByName($companyName);
			if (empty($this->data)) {
				return [];
			}
		} else {
			$this->displayType = 'Summary';
			$this->response['fields'] = [
				'' => \App\Language::translate('LBL_UNITED_KINGDOM_CH_NOT_FOUND_NO_DATA', 'Other.RecordCollector')
			];
			return $this->response;
		}
		$this->loadData();
		return $this->response;
	}

	/**
	 * Function fetching from Companies House API.
	 *
	 * @param string $ncr
	 *
	 * @return array
	 */
	private function getDataFromApiByNcr($ncr): array
	{
		try {
			$response = (\App\RequestHttp::getClient(\App\RequestHttp::getOptions()))->request('GET', $this->url . 'company/' . $ncr, [
				'auth' => [$this->apiKey, ''],
			]);
		} catch (\GuzzleHttp\Exception\ClientException $e) {
			\App\Log::warning($e->getMessage(), 'RecordCollectors');
			$this->response['error'] = $e->getMessage();
		}
		return isset($response) ? $this->parseData(\App\Json::decode($response->getBody()->getContents(), true)) : [];
	}

	/**
	 * Function finding NCR Number by company name.
	 *
	 * @param string $companyName
	 *
	 * @return void
	 */
	private function getDataFromApiByName(string $companyName): void
	{
		try {
			$response = (\App\RequestHttp::getClient(\App\RequestHttp::getOptions()))->request('GET', $this->url . 'advanced-search/companies?company_name_includes=' . $companyName, [
				'auth' => [$this->apiKey, ''],
			]);
		} catch (\GuzzleHttp\Exception\ClientException $e) {
			\App\Log::warning($e->getMessage(), 'RecordCollectors');
			$this->response['error'] = $e->getMessage();
		}
		$response = \App\Json::decode($response->getBody()->getContents());
		$data = [];
		foreach ($response['items'] as $key => $value) {
			$data[$key] = $this->getDataFromApiByNcr($value['company_number']);
			if (self::LIMIT === $key) {
				break;
			}
		}
		$this->data = $data;
	}

	/**
	 * Function parsing data to fields from Companies House API.
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	private function parseData(array $data): array
	{
		if (empty($data)) {
			return [];
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
