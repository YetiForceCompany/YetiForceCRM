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
	protected $fields = [
		'ncr' => [
			'labelModule' => '_Base',
			'label' => 'Registration number 1',
			'typeofdata' => 'V~M',
		],
		'accountname' => [
			'labelModule' => '_Base',
			'label' => 'Account name',
			'typeofdata' => 'V~M',
		]
	];

	/** {@inheritdoc} */
	protected $modulesFieldsMap = [
		'Accounts' => [
			'ncr' => 'registration_number_1',
			'accountname' => 'accountname',
		],
		'Leads' => [
			'ncr' => 'registration_number_1',
			'accountname' => 'company',
		],
		'Vendors' => [
			'ncr' => 'registration_number_1',
			'accountname' => 'vendorname',
		],
		'Competition' => [
			'accountname' => 'subject',
		]
	];

	/** {@inheritdoc} */
	public $formFieldsToRecordMap = [
		'Accounts' => [
			'company_name' => 'accountname',
			'top_hitCompany_name' => 'accountname',
			'company_number' => 'registration_number_1',
			'top_hitCompany_number' => 'registration_number_1',
			'sic_codes0' => 'siccode',
			'top_hitSic_codes0' => 'siccode',
			'registered_office_addressAddress_line_1' => 'addresslevel8a',
			'top_hitRegistered_office_addressAddress_line_1' => 'addresslevel8a',
			'registered_office_addressAddress_line_2' => 'addresslevel3a',
			'top_hitRegistered_office_addressAddress_line_2' => 'addresslevel3a',
			'registered_office_addressLocality' => 'addresslevel5a',
			'top_hitRegistered_office_addressLocality' => 'addresslevel5a',
			'registered_office_addressPostal_code' => 'addresslevel7a',
			'top_hitRegistered_office_addressPostal_code' => 'addresslevel7a',
			'registered_office_addressCountry' => 'addresslevel1a',
			'top_hitRegistered_office_addressCountry' => 'addresslevel1a',
			'registered_office_addressRegion' => 'addresslevel4a',
			'top_hitRegistered_office_addressRegion' => 'addresslevel4a',
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
			'top_hitCompany_name' => 'company',
			'company_number' => 'registration_number_1',
			'top_hitCompany_number' => 'registration_number_1',
			'registered_office_addressAddress_line_1' => 'addresslevel8a',
			'top_hitRegistered_office_addressAddress_line_1' => 'addresslevel8a',
			'registered_office_addressAddress_line_2' => 'addresslevel3a',
			'top_hitRegistered_office_addressAddress_line_2' => 'addresslevel3a',
			'registered_office_addressLocality' => 'addresslevel5a',
			'top_hitRegistered_office_addressLocality' => 'addresslevel5a',
			'registered_office_addressPostal_code' => 'addresslevel7a',
			'top_hitRegistered_office_addressPostal_code' => 'addresslevel7a',
			'registered_office_addressCountry' => 'addresslevel1a',
			'top_hitRegistered_office_addressCountry' => 'addresslevel1a',
			'registered_office_addressRegion' => 'addresslevel4a',
			'top_hitRegistered_office_addressRegion' => 'addresslevel4a',
			'registered_office_addressPo_box' => 'poboxa',
		],
		'Vendors' => [
			'company_name' => 'vendorname',
			'top_hitCompany_name' => 'vendorname',
			'company_number' => 'registration_number_1',
			'top_hitCompany_number' => 'registration_number_1',
			'registered_office_addressAddress_line_1' => 'addresslevel8a',
			'top_hitRegistered_office_addressAddress_line_1' => 'addresslevel8a',
			'registered_office_addressAddress_line_2' => 'addresslevel3a',
			'top_hitRegistered_office_addressAddress_line_2' => 'addresslevel3a',
			'registered_office_addressLocality' => 'addresslevel5a',
			'top_hitRegistered_office_addressLocality' => 'addresslevel5a',
			'registered_office_addressPostal_code' => 'addresslevel7a',
			'top_hitRegistered_office_addressPostal_code' => 'addresslevel7a',
			'registered_office_addressCountry' => 'addresslevel1a',
			'top_hitRegistered_office_addressCountry' => 'addresslevel1a',
			'registered_office_addressRegion' => 'addresslevel4a',
			'top_hitRegistered_office_addressRegion' => 'addresslevel4a',
			'service_addressPo_box' => 'poboxb',
		],
		'Competition' => [
			'company_name' => 'subject',
			'top_hitCompany_name' => 'subject',
			'registered_office_addressAddress_line_1' => 'addresslevel8a',
			'top_hitRegistered_office_addressAddress_line_1' => 'addresslevel8a',
			'registered_office_addressAddress_line_2' => 'addresslevel3a',
			'top_hitRegistered_office_addressAddress_line_2' => 'addresslevel3a',
			'registered_office_addressLocality' => 'addresslevel5a',
			'top_hitRegistered_office_addressLocality' => 'addresslevel5a',
			'registered_office_addressPostal_code' => 'addresslevel7a',
			'top_hitRegistered_office_addressPostal_code' => 'addresslevel7a',
			'registered_office_addressCountry' => 'addresslevel1a',
			'top_hitRegistered_office_addressCountry' => 'addresslevel1a',
			'registered_office_addressRegion' => 'addresslevel4a',
			'top_hitRegistered_office_addressRegion' => 'addresslevel4a',
			'registered_office_addressPo_box' => 'poboxa',
		]
	];

	/** @var array Configuration field list. */
	public $settingsFields = [
		'api_key' => ['required' => 1, 'purifyType' => 'Text', 'label' => 'LBL_API_KEY'],
	];

	/** @var string CH sever address */
	protected $url = 'https://api.company-information.service.gov.uk/';

	/** @var string Url to Documentation API */
	public $docUrl = 'https://developer.company-information.service.gov.uk/';

	/** {@inheritdoc} */
	public function search(): array
	{
		$this->moduleName = $this->request->getModule();
		$ncr = str_replace([' ', ',', '.', '-'], '', $this->request->getByType('ncr', 'Text'));
		$companyName = str_replace([',', '.', '-'], ' ', $this->request->getByType('accountname', 'Text'));

		if (!$ncr && !$companyName) {
			return [];
		}
		$this->getDataFromApi($ncr, $companyName ?? null);
		$this->parseData();
		if (empty($this->data)) {
			return [];
		}
		$this->loadData();
		$this->response['additional'] = $this->data;
		return $this->response;
	}

	/**
	 * Function fetching from Companies House API.
	 *
	 * @param string $ncr
	 * @param string $companyName
	 *
	 * @return void
	 */
	private function getDataFromApi(string $ncr, string $companyName = null): void
	{
		$config = \App\Json::decode((new \App\Db\Query())->select(['params'])->from('vtiger_links')->where(['linktype' => 'EDIT_VIEW_RECORD_COLLECTOR', 'linkurl' => __CLASS__])->scalar(), true);
		try {
			$response = (new \GuzzleHttp\Client(\App\RequestHttp::getOptions()))->request('GET', $this->url . 'company/' . $ncr, [
				'auth' => [$config['api_key'], ''],
			]);
			if (!isset($response) && $companyName) {
				$response = (new \GuzzleHttp\Client(\App\RequestHttp::getOptions()))->request('GET', $this->url . 'advanced-search/companies?company_name_includes=' . $companyName, [
					'auth' => [$config['api_key'], ''],
				]);
			}
		} catch (\GuzzleHttp\Exception\ClientException $e) {
			\App\Log::warning($e->getMessage(), 'RecordCollectors');
			$this->response['error'] = $e->getMessage();
		}

		$this->data = isset($response) ? \App\Json::decode($response->getBody()->getContents(), true) : [];
	}

	/**
	 * Function parsing data to fields from Companies House API.
	 *
	 * @return void
	 */
	private function parseData(): void
	{
		if (empty($this->data)) {
			return;
		}
		$this->data = \App\Utils::flattenKeys($this->data, 'ucfirst');
	}
}
