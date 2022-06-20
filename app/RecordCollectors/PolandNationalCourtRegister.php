<?php
/**
 * Poland National Court Register record collector file.
 *
 * @package App
 *
 * @see https://prs.ms.gov.pl/krs/openApi
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Rembiesa <s.rembiesa@yetiforce.com>
 */

namespace App\RecordCollectors;

/**
 * Poland National Court Register record collector class.
 */
class PolandNationalCourtRegister extends Base
{
	/** {@inheritdoc} */
	protected static $allowedModules = ['Accounts', 'Leads', 'Vendors', 'Competition'];

	/** {@inheritdoc} */
	public $icon = 'fa-solid fa-hammer';

	/** {@inheritdoc} */
	public $label = 'LBL_NCR';

	/** {@inheritdoc} */
	public $displayType = 'FillFields';

	/** {@inheritdoc} */
	public $description = 'LBL_NCR_DESC';

	/** {@inheritdoc} */
	protected $fields = [
		'taxNumber' => [
			'labelModule' => '_Base',
			'label' => 'Tax Number',
			'typeofdata' => 'V~M',
		]
	];

	/** {@inheritdoc} */
	protected $modulesFieldsMap = [
		'Accounts' => [
			'taxNumber' => 'registration_number_1',
		],
		'Leads' => [
			'taxNumber' => 'registration_number_1',
		],
		'Vendors' => [
			'taxNumber' => 'registration_number_1',
		]
	];

	/** {@inheritdoc} */
	public $formFieldsToRecordMap = [
		'Accounts' => [
			'Tax Number' => 'registration_number_2',
			'NCR' => 'registration_number_1',
			'VAT' => 'vat_id',
			'Annual revenue' => 'annual_revenue',
			'SIC code' => 'siccode'
		],
		'Leads' => [
			'Tax Number' => 'registration_number_2',
			'NCR' => 'registration_number_1',
			'VAT' => 'vat_id',
			'Annual revenue' => 'annualrevenue',
		],
		'Vendors' => [
			'Tax Number' => 'registration_number_2',
			'NCR' => 'registration_number_1',
			'VAT' => 'vat_id',
		],
		'Competition' => [
			'VAT' => 'vat_id',
		]
	];

	/** @var string NCR sever address */
	protected $url = 'https://api-krs.ms.gov.pl/api/krs/';

	/** @var array Response from Poland National Court Register API. */
	private $apiData;

	/** {@inheritdoc} */
	public function search(): array
	{
		$response = [];
		$taxNumber = str_replace([' ', ',', '.', '-'], '', $this->request->getByType('taxNumber', 'Text'));
		$moduleName = $this->request->getModule();
		if ($recordId = $this->request->getInteger('record')) {
			$recordModel = \Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
			$response['recordModel'] = $recordModel;
			$fields = $recordModel->getModule()->getFields();
		} else {
			$fields = \Vtiger_Module_Model::getInstance($moduleName)->getFields();
		}

		if (!$taxNumber) {
			return [];
		}

		$infoFromNcr = $this->getAndParse($taxNumber);

		$data = $skip = [];
		foreach ($this->formFieldsToRecordMap[$moduleName] as $label => $fieldName) {
			if (empty($fields[$fieldName]) || !$fields[$fieldName]->isActiveField()) {
				$skip[$fieldName]['label'] = \App\Language::translate($fields[$fieldName]->getFieldLabel(), $moduleName) ?? $fieldName;
			}
			$fieldModel = $fields[$fieldName];
			$data[$fieldName]['label'] = \App\Language::translate($fieldModel->getFieldLabel(), $moduleName);
			$data[$fieldName]['data'][0] = [
				'raw' => $fieldModel->getEditViewDisplayValue($infoFromNcr[$label]),
				'display' => $fieldModel->getDisplayValue($infoFromNcr[$label]),
			];
		}

		$response['fields'] = $data;
		$response['keys'] = [0];
		$response['skip'] = $skip;
		$response['additional'] = $this->getAdditional();

		return $response;
	}

	/**
	 * Function fetching and parsing data from Poland National Court Register API to YetiForce fields.
	 *
	 * @param mixed $taxNumber
	 *
	 * @return array
	 */
	private function getAndParse($taxNumber): array
	{
		try {
			$res = (new \GuzzleHttp\Client(\App\RequestHttp::getOptions()))->request('GET', $this->url . 'OdpisAktualny/' . $taxNumber, [
				'verify' => false,
				'query' => [
					'rejestr' => 'P',
					'format' => 'json'
				]
			]);
		} catch (\GuzzleHttp\Exception\ClientException $e) {
			\App\Log::warning($e->getMessage(), 'RecordCollectors');
			$response['error'] = $e->getMessage();
		}

		$this->apiData = \App\Json::decode($res->getBody()->getContents());

		if (isset($this->apiData['odpis']['dane']['dzial3']['przedmiotDzialalnosci']['przedmiotPrzewazajacejDzialalnosci'][0])) {
			$pkd = $this->apiData['odpis']['dane']['dzial3']['przedmiotDzialalnosci']['przedmiotPrzewazajacejDzialalnosci'][0];
		} elseif (isset($this->apiData['odpis']['dane']['dzial3']['przedmiotDzialalnosci']['przedmiotPozostalejDzialalnosci'][0])) {
			$pkd = $this->apiData['odpis']['dane']['dzial3']['przedmiotDzialalnosci']['przedmiotPozostalejDzialalnosci'][0];
		} else {
			$pkd = null;
		}

		$annualRevenue = isset($this->apiData['odpis']['dane']['dzial1']['kapital']) ? (float) $this->apiData['odpis']['dane']['dzial1']['kapital']['wysokoscKapitaluZakladowego']['wartosc'] : null;

		return [
			'Tax Number' => $this->apiData['odpis']['dane']['dzial1']['danePodmiotu']['identyfikatory']['regon'],
			'VAT' => $this->apiData['odpis']['dane']['dzial1']['danePodmiotu']['identyfikatory']['nip'],
			'NCR' => $this->apiData['odpis']['naglowekA']['numerKRS'],
			'Annual revenue' => $annualRevenue,
			'SIC code' => $pkd ? $pkd['kodDzial'] . '.' . $pkd['kodKlasa'] . '.' . $pkd['kodPodklasa'] : ''
		];
	}

	/**
	 * Get Additional fields from API Poland National Court Register response.
	 *
	 * @return array
	 */
	private function getAdditional(): array
	{
		if (!isset($this->apiData)) {
			return [];
		}

		$additional = [];
		foreach ($this->apiData['odpis']['naglowekA'] as $key => $value) {
			$additional[$key] = $value;
		}

		return $additional;
	}
}
