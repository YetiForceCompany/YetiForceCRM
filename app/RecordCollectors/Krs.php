<?php
/**
 * KRS record collector file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Rembiesa <s.rembiesa@yetiforce.com>
 */

namespace App\RecordCollectors;

/**
 * KRS record collector class.
 */
class Krs extends Base
{
	/** {@inheritdoc} */
	protected static $allowedModules = ['Accounts', 'Leads', 'Vendors'];

	/** {@inheritdoc} */
	public $icon = 'fa-solid fa-hammer';

	/** {@inheritdoc} */
	public $label = 'LBL_KRS';

	/** {@inheritdoc} */
	public $displayType = 'FillFields';

	/** {@inheritdoc} */
	public $description = 'LBL_KRS_DESC';

	/**
	 * Response from KRS API.
	 *
	 * @var array
	 */
	private $apiData;

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
			'Regon' => 'registration_number_2',
			'KRS' => 'registration_number_1',
			'Nip' => 'vat_id',
			'Kapitał' => 'annual_revenue',
			'Numer PKD' => 'siccode'
		],
		'Leads' => [
			'Regon' => 'registration_number_2',
			'KRS' => 'registration_number_1',
			'Nip' => 'vat_id',
			'Kapitał' => 'annualrevenue',
		],
		'Vendors' => [
			'Regon' => 'registration_number_2',
			'KRS' => 'registration_number_1',
			'Nip' => 'vat_id',
		]
	];

	/** @var string KRS sever address */
	protected $url = 'https://api-krs.ms.gov.pl/api/krs/';

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

		$infoFromKrs = $this->getAndParse($taxNumber);

		$data = $skip = [];
		foreach ($this->formFieldsToRecordMap[$moduleName] as $label => $fieldName) {
			if (empty($fields[$fieldName]) || !$fields[$fieldName]->isActiveField()) {
				$skip[$fieldName]['label'] = \App\Language::translate($fields[$fieldName]->getFieldLabel(), $moduleName) ?? $fieldName;
			}
			$fieldModel = $fields[$fieldName];
			$data[$fieldName]['label'] = \App\Language::translate($fieldModel->getFieldLabel(), $moduleName);
			$data[$fieldName]['data'][0] = [
				'raw' => $fieldModel->getEditViewDisplayValue($infoFromKrs[$label]),
				'display' => $fieldModel->getDisplayValue($infoFromKrs[$label]),
			];
		}

		$response['fields'] = $data;
		$response['keys'] = [0];
		$response['skip'] = $skip;
		$response['additional'] = $this->getAdditional();

		return $response;
	}

	/**
	 * Function fetching and parsing data from KRS API to YetiForce fields.
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
			'Regon' => $this->apiData['odpis']['dane']['dzial1']['danePodmiotu']['identyfikatory']['regon'],
			'Nip' => $this->apiData['odpis']['dane']['dzial1']['danePodmiotu']['identyfikatory']['nip'],
			'KRS' => $this->apiData['odpis']['naglowekA']['numerKRS'],
			'Kapitał' => $annualRevenue,
			'Numer PKD' => $pkd ? $pkd['kodDzial'] . '.' . $pkd['kodKlasa'] . '.' . $pkd['kodPodklasa'] : ''
		];
	}

	/**
	 * Get Additional fields from API KRS response.
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
