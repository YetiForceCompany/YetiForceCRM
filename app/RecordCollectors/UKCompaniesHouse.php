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
		]
	];

	/** {@inheritdoc} */
	protected $modulesFieldsMap = [
		'Accounts' => [
			'ncr' => 'registration_number_1',
		],
		'Leads' => [
			'ncr' => 'registration_number_1',
		],
		'Vendors' => [
			'ncr' => 'registration_number_1',
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

	/** @var string CH sever address */
	protected $url = 'https://api.company-information.service.gov.uk/';

	/** {@inheritdoc} */
	public function search(): array
	{
		$this->moduleName = $this->request->getModule();
		$ncr = str_replace([' ', ',', '.', '-'], '', $this->request->getByType('ncr', 'Text'));
		if (!$ncr) {
			return [];
		}
		$this->getDataFromApi($ncr);
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
	 *
	 * @return void
	 */
	private function getDataFromApi($ncr): void
	{
		try {
			$response = (new \GuzzleHttp\Client(\App\RequestHttp::getOptions()))->request('GET', $this->url . 'company/' . $ncr, [
				'auth' => ['API_KEY_HERE', ''],
			]);
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
