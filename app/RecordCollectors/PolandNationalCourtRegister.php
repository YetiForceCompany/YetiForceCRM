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
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\RecordCollectors;

/**
 * Poland National Court Register record collector class.
 */
class PolandNationalCourtRegister extends Base
{
	/** {@inheritdoc} */
	protected static $allowedModules = ['Accounts', 'Leads', 'Vendors'];

	/** {@inheritdoc} */
	public $icon = 'fa-solid fa-hammer';

	/** {@inheritdoc} */
	public $label = 'LBL_POLAND_NCR';

	/** {@inheritdoc} */
	public $displayType = 'FillFields';

	/** {@inheritdoc} */
	public $description = 'LBL_POLAND_NCR_DESC';

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
			'SIC code' => 'siccode',
			'Street' => 'addresslevel8a',
			'Building number' => 'buildingnumbera',
			'Office Number' => 'localnumbera',
			'City/Village' => 'addresslevel5a',
			'Post Code' => 'addresslevel7a',
			'State' => 'addresslevel2a',
			'Country' => 'addresslevel1a',
			'Township' => 'addresslevel4a',
			'County' => 'addresslevel3a'
		],
		'Leads' => [
			'Tax Number' => 'registration_number_2',
			'NCR' => 'registration_number_1',
			'VAT' => 'vat_id',
			'Annual revenue' => 'annualrevenue',
			'Street' => 'addresslevel8a',
			'Building number' => 'buildingnumbera',
			'Office Number' => 'localnumbera',
			'City/Village' => 'addresslevel5a',
			'Post Code' => 'addresslevel7a',
			'State' => 'addresslevel2a',
			'Country' => 'addresslevel1a',
			'Township' => 'addresslevel4a',
			'County' => 'addresslevel3a'
		],
		'Vendors' => [
			'Tax Number' => 'registration_number_2',
			'NCR' => 'registration_number_1',
			'VAT' => 'vat_id',
			'Street' => 'addresslevel8a',
			'Building number' => 'buildingnumbera',
			'Office Number' => 'localnumbera',
			'City/Village' => 'addresslevel5a',
			'Post Code' => 'addresslevel7a',
			'State' => 'addresslevel2a',
			'Country' => 'addresslevel1a',
			'Township' => 'addresslevel4a',
			'County' => 'addresslevel3a'
		]
	];

	/** @var string NCR sever address */
	protected $url = 'https://api-krs.ms.gov.pl/api/krs/';

	/** @var array Data from Poland National Court Register API. */
	private $apiData = [];

	/** @var array Response. */
	private $response = [];

	/** {@inheritdoc} */
	public function search(): array
	{
		$taxNumber = str_replace([' ', ',', '.', '-'], '', $this->request->getByType('taxNumber', 'Text'));
		$moduleName = $this->request->getModule();
		if ($recordId = $this->request->getInteger('record')) {
			$recordModel = \Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
			$this->response['recordModel'] = $recordModel;
			$fields = $recordModel->getModule()->getFields();
		} else {
			$fields = \Vtiger_Module_Model::getInstance($moduleName)->getFields();
		}
		if (!$taxNumber) {
			return [];
		}

		$this->getDataFromApi($taxNumber);
		$infoFromNcr = $this->parseData();

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

		$this->response['fields'] = $data;
		$this->response['keys'] = [0];
		$this->response['skip'] = $skip;
		$this->response['additional'] = $this->getAdditional();
		return $this->response;
	}

	/**
	 * Function fetching from Poland National Court Register API.
	 *
	 * @param mixed $taxNumber
	 *
	 * @return void
	 */
	private function getDataFromApi($taxNumber): void
	{
		try {
			$responseData = (new \GuzzleHttp\Client(\App\RequestHttp::getOptions()))
				->request('GET', "{$this->url}OdpisAktualny/{$taxNumber}?rejestr=P&format=json");
			$this->apiData = \App\Json::decode($responseData->getBody()->getContents());
		} catch (\GuzzleHttp\Exception\ClientException $e) {
			\App\Log::warning($e->getMessage(), 'RecordCollectors');
			$this->response['error'] = $e->getMessage();
		}
	}

	/**
	 * Function parsing data to YetiForce fields from Poland National Court Register API.
	 *
	 * @return array
	 */
	private function parseData(): array
	{
		if (empty($this->apiData['odpis'])) {
			return [];
		}
		if (isset($this->apiData['odpis']['dane']['dzial3']['przedmiotDzialalnosci']['przedmiotPrzewazajacejDzialalnosci'][0])) {
			$pkd = $this->apiData['odpis']['dane']['dzial3']['przedmiotDzialalnosci']['przedmiotPrzewazajacejDzialalnosci'][0];
		} elseif (isset($this->apiData['odpis']['dane']['dzial3']['przedmiotDzialalnosci']['przedmiotPozostalejDzialalnosci'][0])) {
			$pkd = $this->apiData['odpis']['dane']['dzial3']['przedmiotDzialalnosci']['przedmiotPozostalejDzialalnosci'][0];
		} else {
			$pkd = null;
		}
		return [
			'Tax Number' => $this->apiData['odpis']['dane']['dzial1']['danePodmiotu']['identyfikatory']['regon'],
			'VAT' => $this->apiData['odpis']['dane']['dzial1']['danePodmiotu']['identyfikatory']['nip'],
			'NCR' => $this->apiData['odpis']['naglowekA']['numerKRS'],
			'Annual revenue' => isset($this->apiData['odpis']['dane']['dzial1']['kapital']) ? (float) $this->apiData['odpis']['dane']['dzial1']['kapital']['wysokoscKapitaluZakladowego']['wartosc'] : null,
			'SIC code' => $pkd ? $pkd['kodDzial'] . '.' . $pkd['kodKlasa'] . '.' . $pkd['kodPodklasa'] : '',
			'Street' => $this->apiData['odpis']['dane']['dzial1']['siedzibaIAdres']['adres']['ulica'] ?? '',
			'Building number' => $this->apiData['odpis']['dane']['dzial1']['siedzibaIAdres']['adres']['nrDomu'] ?? '',
			'Office Number' => $this->apiData['odpis']['dane']['dzial1']['siedzibaIAdres']['adres']['nrLokalu'] ?? '',
			'City/Village' => $this->apiData['odpis']['dane']['dzial1']['siedzibaIAdres']['adres']['miejscowosc'] ?? '',
			'Post Code' => $this->apiData['odpis']['dane']['dzial1']['siedzibaIAdres']['adres']['kodPocztowy'] ?? '',
			'State' => $this->apiData['odpis']['dane']['dzial1']['siedzibaIAdres']['siedziba']['wojewodztwo'] ?? '',
			'Country' => $this->apiData['odpis']['dane']['dzial1']['siedzibaIAdres']['siedziba']['kraj'] ?? '',
			'Township' => $this->apiData['odpis']['dane']['dzial1']['siedzibaIAdres']['siedziba']['gmina'] ?? '',
			'County' => $this->apiData['odpis']['dane']['dzial1']['siedzibaIAdres']['siedziba']['powiat'] ?? ''
		];
	}

	/**
	 * Get Additional fields from API Poland National Court Register response.
	 *
	 * @return array
	 */
	private function getAdditional(): array
	{
		if (empty($this->apiData)) {
			return [];
		}
		$additional = [];
		if (isset($this->apiData['odpis']['naglowekA'])) {
			foreach ($this->apiData['odpis']['naglowekA'] as $key => $value) {
				$additional[$key] = $value;
			}
		}

		if (isset($this->apiData['odpis']['dane']['dzial1']['danePodmiotu'])) {
			foreach ($this->apiData['odpis']['dane']['dzial1']['danePodmiotu'] as $key => $value) {
				if ('identyfikatory' === $key) {
					continue;
				}
				$additional[$key] = $value;
			}
		}

		if (isset($this->apiData['odpis']['dane']['dzial1']['siedzibaIAdres'])) {
			foreach ($this->apiData['odpis']['dane']['dzial1']['siedzibaIAdres']['siedziba'] as $key => $value) {
				$additional[$key] = $value;
			}

			foreach ($this->apiData['odpis']['dane']['dzial1']['siedzibaIAdres']['adres'] as $key => $value) {
				$additional[$key] = $value;
			}
		}

		$additional['adresStronyInternetowej'] = $this->apiData['odpis']['dane']['dzial1']['siedzibaIAdres']['adresStronyInternetowej'] ?? '';
		$additional['opisSposobuPowstaniaInformacjaOUchwale'] = $this->apiData['odpis']['dane']['dzial1']['sposobPowstaniaPodmiotu']['opisSposobuPowstaniaInformacjaOUchwale'] ?? '';
		$additional['wysokoscKapitaluDocelowegoZapasowego'] = $this->apiData['odpis']['dane']['dzial1']['kapital']['wysokoscKapitaluDocelowegoZapasowego']['wartosc'] ?? '';
		$additional['lacznaLiczbaAkcjiUdzialow'] = $this->apiData['odpis']['dane']['dzial1']['kapital']['lacznaLiczbaAkcjiUdzialow'] ?? '';
		$additional['wartoscJednejAkcji'] = $this->apiData['odpis']['dane']['dzial1']['kapital']['wartoscJednejAkcji']['wartosc'] ?? '';
		$additional['czescKapitaluWplaconegoPokrytego'] = $this->apiData['odpis']['dane']['dzial1']['kapital']['czescKapitaluWplaconegoPokrytego']['wartosc'] ?? '';

		if (isset($this->apiData['odpis']['dane']['dzial3']['przedmiotDzialalnosci']['przedmiotPrzewazajacejDzialalnosci'][0])) {
			$mainPkd = $this->apiData['odpis']['dane']['dzial3']['przedmiotDzialalnosci']['przedmiotPrzewazajacejDzialalnosci'][0];
			$additional['przedmiotPrzewazajacejDzialalnosci'] = "{$mainPkd['opis']}  ({$mainPkd['kodDzial']}.{$mainPkd['kodKlasa']}.{$mainPkd['kodPodklasa']})";
		}

		if (isset($this->apiData['odpis']['dane']['dzial3']['przedmiotDzialalnosci']['przedmiotPozostalejDzialalnosci'])) {
			$i = 1;
			foreach ($this->apiData['odpis']['dane']['dzial3']['przedmiotDzialalnosci']['przedmiotPozostalejDzialalnosci'] as $pkd) {
				$additional["przedmiotPozostalejDzialalnosci{$i}"] = "{$pkd['opis']}  ({$pkd['kodDzial']}.{$pkd['kodKlasa']}.{$pkd['kodPodklasa']})";
				++$i;
			}
		}

		return $additional;
	}
}
