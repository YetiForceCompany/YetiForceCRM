<?php
/**
 * United Kingdom Companies House record collector file.
 *
 * @see https://developer.company-information.service.gov.uk/
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
 * United Kingdom Companies House record collector class.
 */
class UkCompaniesHouse extends Base
{
	/** {@inheritdoc} */
	public $allowedModules = ['Accounts', 'Leads', 'Vendors', 'Competition'];

	/** {@inheritdoc} */
	public $icon = 'yfi-companies-house-uk';

	/** {@inheritdoc} */
	public $label = 'LBL_UK_CH';

	/** {@inheritdoc} */
	public $displayType = 'FillFields';

	/** {@inheritdoc} */
	public $description = 'LBL_UK_CH_DESC';

	/** {@inheritdoc} */
	public $docUrl = 'https://developer.company-information.service.gov.uk/';

	/** @var string CH sever address */
	private $url = 'https://api.company-information.service.gov.uk';

	/** {@inheritdoc} */
	public $settingsFields = [
		'api_key' => ['required' => 1, 'purifyType' => 'Text', 'label' => 'LBL_API_KEY'],
	];

	/** @var string Api Key. */
	private $apiKey;

	/** @var string CH sever address */
	const EXTERNAL_URL = 'https://find-and-update.company-information.service.gov.uk/company/';

	/** {@inheritdoc} */
	protected $fields = [
		'ncr' => [
			'labelModule' => '_Base',
			'label' => 'Registration number 1',
		],
		'companyName' => [
			'labelModule' => '_Base',
			'label' => 'Account name',
		]
	];

	/** {@inheritdoc} */
	protected $modulesFieldsMap = [
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
			'registered_office_addressPostal_code' => 'addresslevel7a',
			'registered_office_addressLocality' => 'addresslevel5a',
			'registered_office_addressRegion' => 'addresslevel4a',
			'registered_office_addressAddress_line_2' => 'addresslevel3a',
			'registered_office_addressCountry' => 'addresslevel1a',
			'registered_office_addressPo_box' => 'poboxa',
			'service_addressAddress_line_1' => 'addresslevel8b',
			'service_addressAddress_line_2' => 'addresslevel3b',
			'service_addressLocality' => 'addresslevel5b',
			'service_addressPostal_code' => 'addresslevel7b',
			'service_addressCountry' => 'addresslevel1b',
			'service_addressRegion' => 'addresslevel4b',
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
			'service_addressAddress_line_1' => 'addresslevel8b',
			'service_addressAddress_line_2' => 'addresslevel3b',
			'service_addressLocality' => 'addresslevel5b',
			'service_addressPostal_code' => 'addresslevel7b',
			'service_addressCountry' => 'addresslevel1b',
			'service_addressRegion' => 'addresslevel4b',
			'service_addressPo_box' => 'poboxb',
		],
		'Competition' => [
			'company_name' => 'subject',
			'registered_office_addressAddress_line_1' => 'addresslevel8a',
			'registered_office_addressPostal_code' => 'addresslevel7a',
			'registered_office_addressLocality' => 'addresslevel5a',
			'registered_office_addressRegion' => 'addresslevel4a',
			'registered_office_addressAddress_line_2' => 'addresslevel3a',
			'registered_office_addressCountry' => 'addresslevel1a',
			'registered_office_addressPo_box' => 'poboxa',
		]
	];

	/** @var string[] Keys to skip in additional */
	const REMOVE_KEYS = ['linksSelf', 'linksFiling_history', 'linksOfficers', 'linksPersons_with_significant_control-statements', 'linksCharges'];

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
		if (!$this->isActive()) {
			return [];
		}
		$this->setApiKey();
		$this->moduleName = $this->request->getModule();
		$ncr = str_replace([' ', ',', '.', '-'], '', $this->request->getByType('ncr', 'Text'));
		$companyName = str_replace([',', '.', '-'], ' ', $this->request->getByType('companyName', 'Text'));

		if ($ncr) {
			$this->data = $this->getDataFromApiByNcr($ncr);
		} elseif ($companyName) {
			$this->getDataFromApiByName($companyName);
		} else {
			$this->displayType = 'Summary';
			$this->response['fields'] = [
				'' => \App\Language::translate('LBL_UK_CH_NOT_FOUND_NO_DATA', 'Other.RecordCollector')
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
			$response = \App\RequestHttp::getClient()->request('GET', $this->url . '/company/' . $ncr, [
				'auth' => [$this->apiKey, ''],
			]);
		} catch (\GuzzleHttp\Exception\GuzzleException $e) {
			\App\Log::warning($e->getMessage(), 'RecordCollectors');
			$this->response['error'] = $e->getMessage();
		}
		$data = isset($response) ? $this->parseData(\App\Json::decode($response->getBody()->getContents(), true)) : [];
		if (!empty($data)) {
			foreach (self::REMOVE_KEYS as $key) {
				if (isset($data[$key])) {
					unset($data[$key]);
				}
			}
			if (isset($data['linksPersons_with_significant_control'])) {
				foreach ($this->getPersonsWithSignificantControl($data['linksPersons_with_significant_control']) as $name) {
					$data['linksPersons_with_significant_control'] .= ' ' . $name;
				}
			}
		}
		return $data;
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
			$response = \App\RequestHttp::getClient()->request('GET', $this->url . '/advanced-search/companies?company_name_includes=' . $companyName, [
				'auth' => [$this->apiKey, ''],
			]);
		} catch (\GuzzleHttp\Exception\GuzzleException $e) {
			\App\Log::warning($e->getMessage(), 'RecordCollectors');
			$this->response['error'] = $e->getMessage();
		}
		$response = \App\Json::decode($response->getBody()->getContents());
		$data = [];
		foreach ($response['items'] as $key => $value) {
			$data[$key] = $this->getDataFromApiByNcr($value['company_number']);
			$this->response['links'][$key] = self::EXTERNAL_URL . $data[$key]['company_number'];
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

	/**
	 * Function fetching persons Persons with significant control .
	 *
	 * @param string $url
	 *
	 * @return array
	 */
	private function getPersonsWithSignificantControl(string $url): array
	{
		try {
			$response = \App\RequestHttp::getClient()->request('GET', $this->url . $url, [
				'auth' => [$this->apiKey, ''],
			]);
		} catch (\GuzzleHttp\Exception\GuzzleException $e) {
			\App\Log::warning($e->getMessage(), 'RecordCollectors');
			$this->response['error'] = $e->getMessage();
		}
		$names = [];
		$items = \App\Json::decode($response->getBody()->getContents())['items'];
		foreach ($items as $item) {
			if ('individual-person-with-significant-control' === $item['kind']) {
				$names[] = $item['name'];
			}
		}
		return $names;
	}
}
