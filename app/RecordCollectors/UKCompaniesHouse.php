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
	public $icon = 'fas fa-house';

	/** {@inheritdoc} */
	public $label = 'LBL_UNITED_KINGDOM_CH';

	/** {@inheritdoc} */
	public $displayType = 'FillFields';

	/** {@inheritdoc} */
	public $description = 'LBL_UNITED_KINGDOM_CH_DESC';

	/** @var array Data from Companies House API. */
	private $apiData = [];

	/** @var array parsed data from CH API. */
	private $data = [];

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
			'companyName' => 'accountname',
			'ncr' => 'registration_number_1',
			'sicCode' => 'siccode',
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
		$ncr = str_replace([' ', ',', '.', '-'], '', $this->request->getByType('ncr', 'Text'));
		$moduleName = $this->request->getModule();
		if ($recordId = $this->request->getInteger('record')) {
			$recordModel = \Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
			$this->response['recordModel'] = $recordModel;
			$fields = $recordModel->getModule()->getFields();
		} else {
			$fields = \Vtiger_Module_Model::getInstance($moduleName)->getFields();
		}
		if (!$ncr) {
			return [];
		}

		$this->getDataFromApi($ncr);
		if (empty($this->apiData)) {
			return [];
		}
		$this->parseData();

		$fieldsData = $skip = [];
		foreach ($this->formFieldsToRecordMap[$moduleName] as $label => $fieldName) {
			if (!isset($this->data[$label])) {
				continue;
			}
			if (empty($fields[$fieldName]) || !$fields[$fieldName]->isActiveField()) {
				$skip[$fieldName]['label'] = \App\Language::translate($fields[$fieldName]->getFieldLabel(), $moduleName) ?? $fieldName;
			}
			$fieldModel = $fields[$fieldName];
			$fieldsData[$fieldName]['label'] = \App\Language::translate($fieldModel->getFieldLabel(), $moduleName);
			$fieldsData[$fieldName]['data'][0] = [
				'raw' => $fieldModel->getEditViewDisplayValue($this->data[$label]),
				'display' => $fieldModel->getDisplayValue($this->data[$label]),
			];
		}

		$this->response['fields'] = $fieldsData;
		$this->response['keys'] = [0];
		$this->response['skip'] = $skip;
		$this->response['additional'] = $this->getAdditional();
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
				'auth' => ['API_KEY_HERE', '8f27bb27-4af7-44c7-ad71-ed59d5c15ab2'],
			]);
		} catch (\GuzzleHttp\Exception\ClientException $e) {
			\App\Log::warning($e->getMessage(), 'RecordCollectors');
			$this->response['error'] = $e->getMessage();
		}
		$this->apiData = isset($response) ? \App\Json::decode($response->getBody()->getContents(), true) : [];
	}

	/**
	 * Function parsing data to fields from Companies House API.
	 *
	 * @return void
	 */
	private function parseData(): void
	{
		$this->data['companyName'] = $this->apiData['company_name'];
		$this->data['ncr'] = $this->apiData['company_number'];
		$this->data['sicCode'] = $this->apiData['sic_codes'][0] ?? null;
		$this->data['country'] = $this->apiData['registered_office_address']['country'];
		$this->data['city'] = $this->apiData['registered_office_address']['locality'];
		$this->data['postCode'] = $this->apiData['registered_office_address']['postal_code'];
		$this->data['street'] = $this->apiData['registered_office_address']['address_line_1'];
		$this->data['county'] = $this->apiData['registered_office_address']['address_line_2'] ?? null;
		$this->data['township'] = $this->apiData['registered_office_address']['region'] ?? null;
		$this->data['poBox'] = $this->apiData['registered_office_address']['po_box'] ?? null;

		if (isset($this->apiData['service_address'])) {
			$this->data['countryDelivery'] = $this->apiData['service_address']['country'];
			$this->data['cityDelivery'] = $this->apiData['service_address']['locality'];
			$this->data['postCodeDelivery'] = $this->apiData['service_address']['postal_code'];
			$this->data['streetDelivery'] = $this->apiData['service_address']['address_line_1'];
			$this->data['countyDelivery'] = $this->apiData['service_address']['address_line_2'] ?? null;
			$this->data['townshipDelivery'] = $this->apiData['service_address']['region'] ?? null;
			$this->data['poBoxDelivery'] = $this->apiData['service_address']['po_box'] ?? null;

			unset($this->apiData['service_address']);
		}
		unset($this->apiData['company_name'], $this->apiData['company_number'], $this->apiData['sic_codes'][0], $this->apiData['registered_office_address']);
	}

	/**
	 * Get Additional fields from API Companies House response.
	 *
	 * @return array
	 */
	private function getAdditional(): array
	{
		$additional = [];
		foreach ($this->apiData as $key => $value) {
			if ('foreign_company_details' === $key) {
				continue;
			}
			switch ($key) {
				case 'accounts':
					foreach ($this->apiData[$key] as $sectionKey => $sectionValue) {
						if ('array' === \gettype($sectionValue)) {
							$additional[$sectionKey] = implode(' ', $sectionValue);
						} else {
							$additional['accounts ' . $sectionKey] = $sectionValue;
						}
					}
					break;
				case 'annual_return':
				case 'branch_company_details':
				case 'confirmation_statement':
				case 'links':
				case 'sic_codes':
					foreach ($this->apiData[$key] as $sectionKey => $sectionValue) {
						$additional[$key . ' ' . $sectionKey] = $sectionValue;
					}
					break;
				case 'previous_company_names':
					$i = 1;
					foreach ($this->apiData[$key] as $previousName) {
						$additional["previous_company_names{$i}"] = implode(' ', $previousName);
						++$i;
					}
					break;
				default:
					break;
			}
			$additional['jurisdiction'] = $value;
			$additional['company_status'] = $value;
			$additional['date_of_creation'] = $value;
		}
		return $additional;
	}
}
