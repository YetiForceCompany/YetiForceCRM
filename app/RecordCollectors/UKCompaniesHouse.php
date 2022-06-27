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
			'street' => 'addresslevel8a',
			'city' => 'addresslevel5a',
			'registered_office_addressPostal_code' => 'addresslevel7a',
			'country' => 'addresslevel1a',
			'township' => 'addresslevel4a',
			'county' => 'addresslevel3a',
			'streetDelivery' => 'addresslevel8c',
			'cityDelivery' => 'addresslevel5c',
			'postCodeDelivery' => 'addresslevel7c',
			'countryDelivery' => 'addresslevel1c',
			'townshipDelivery' => 'addresslevel4c',
			'countyDelivery' => 'addresslevel3c',
			'countryDelivery' => 'addresslevel1c',
		],
		'Leads' => [
			'companyName' => 'company',
			'ncr' => 'registration_number_1',
			'street' => 'addresslevel8a',
			'city' => 'addresslevel5a',
			'postCode' => 'addresslevel7a',
			'country' => 'addresslevel1a',
			'township' => 'addresslevel4a',
			'county' => 'addresslevel3a',
		],
		'Vendors' => [
			'companyName' => 'vendorname',
			'ncr' => 'registration_number_1',
			'street' => 'addresslevel8a',
			'city' => 'addresslevel5a',
			'postCode' => 'addresslevel7a',
			'country' => 'addresslevel1a',
			'township' => 'addresslevel4a',
			'county' => 'addresslevel3a',
			'streetDelivery' => 'addresslevel8c',
			'cityDelivery' => 'addresslevel5c',
			'postCodeDelivery' => 'addresslevel7c',
			'countryDelivery' => 'addresslevel1c',
			'townshipDelivery' => 'addresslevel4c',
			'countyDelivery' => 'addresslevel3c',
			'countryDelivery' => 'addresslevel1c',
		],
		'Competition' => [
			'companyName' => 'subject',
			'street' => 'addresslevel8a',
			'city' => 'addresslevel5a',
			'postCode' => 'addresslevel7a',
			'country' => 'addresslevel1a',
			'township' => 'addresslevel4a',
			'county' => 'addresslevel3a',
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
