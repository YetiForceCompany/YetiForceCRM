<?php
/**
 * Polish National Court Register record collector file.
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
 * Polish National Court Register record collector class.
 */
class PLNationalCourtRegister extends Base
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
			'label' => 'Registration number 1',
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
			'Company' => 'accountname',
			'Tax Number' => 'registration_number_2',
			'NCR' => 'registration_number_1',
			'VAT' => 'vat_id',
			'Web Site' => 'website',
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
			'County' => 'addresslevel3a',
		],
		'Leads' => [
			'Company' => 'company',
			'Tax Number' => 'registration_number_2',
			'NCR' => 'registration_number_1',
			'VAT' => 'vat_id',
			'Annual revenue' => 'annualrevenue',
			'Web Site' => 'website',
			'Street' => 'addresslevel8a',
			'Building number' => 'buildingnumbera',
			'Office Number' => 'localnumbera',
			'City/Village' => 'addresslevel5a',
			'Post Code' => 'addresslevel7a',
			'State' => 'addresslevel2a',
			'Country' => 'addresslevel1a',
			'Township' => 'addresslevel4a',
			'County' => 'addresslevel3a',
		],
		'Vendors' => [
			'Company' => 'vendorname',
			'Tax Number' => 'registration_number_2',
			'NCR' => 'registration_number_1',
			'VAT' => 'vat_id',
			'Web Site' => 'website',
			'Street' => 'addresslevel8a',
			'Building number' => 'buildingnumbera',
			'Office Number' => 'localnumbera',
			'City/Village' => 'addresslevel5a',
			'Post Code' => 'addresslevel7a',
			'State' => 'addresslevel2a',
			'Country' => 'addresslevel1a',
			'Township' => 'addresslevel4a',
			'County' => 'addresslevel3a',
		]
	];
	/** @var array Fields from request. */
	public $data = [];

	/** @var string NCR sever address */
	protected $url = 'https://api-krs.ms.gov.pl/api/krs/';

	/** @var array Data from Polish National Court Register API. */
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
		$this->parseData();

		if (empty($this->data)) {
			return [];
		}
		$fieldsData = $skip = [];
		foreach ($this->formFieldsToRecordMap[$moduleName] as $label => $fieldName) {
			if (!$this->data[$label]) {
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
		$additional = $this->getAdditional();
		foreach ($additional as $key => $value) {
			if (null === $value || '' === $value) {
				unset($additional[$key]);
			}
		}
		$this->response['fields'] = $fieldsData;
		$this->response['keys'] = [0];
		$this->response['skip'] = $skip;
		$this->response['additional'] = $additional;
		return $this->response;
	}

	/**
	 * Function fetching from Polish National Court Register API.
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
			$this->apiData = \App\Json::decode($responseData->getBody()->getContents())['odpis'] ?? [];
		} catch (\GuzzleHttp\Exception\ClientException $e) {
			\App\Log::warning($e->getMessage(), 'RecordCollectors');
			$this->response['error'] = $e->getMessage();
		}
	}

	/**
	 * Function parsing data to fields from Polish National Court Register API.
	 *
	 * @return void
	 */
	private function parseData(): void
	{
		if (empty($this->apiData)) {
			return;
		}
		if (isset($this->apiData['dane']['dzial3']['przedmiotDzialalnosci']['przedmiotPrzewazajacejDzialalnosci'][0])) {
			$pkd = $this->apiData['dane']['dzial3']['przedmiotDzialalnosci']['przedmiotPrzewazajacejDzialalnosci'][0];
		} elseif (isset($this->apiData['dane']['dzial3']['przedmiotDzialalnosci']['przedmiotPozostalejDzialalnosci'][0])) {
			$pkd = $this->apiData['dane']['dzial3']['przedmiotDzialalnosci']['przedmiotPozostalejDzialalnosci'][0];
		} else {
			$pkd = null;
		}
		$this->data['SIC code'] = $pkd ? $pkd['kodDzial'] . '.' . $pkd['kodKlasa'] . '.' . $pkd['kodPodklasa'] : null;
		$this->data['Tax Number'] = $this->apiData['dane']['dzial1']['danePodmiotu']['identyfikatory']['regon'] ?? '';
		$this->data['VAT'] = $this->apiData['dane']['dzial1']['danePodmiotu']['identyfikatory']['nip'] ?? '';
		$this->data['NCR'] = $this->apiData['naglowekA']['numerKRS'];
		$this->data['Annual revenue'] = isset($this->apiData['dane']['dzial1']['kapital']) ? (float) $this->apiData['dane']['dzial1']['kapital']['wysokoscKapitaluZakladowego']['wartosc'] : null;
		$this->data['Street'] = $this->apiData['dane']['dzial1']['siedzibaIAdres']['adres']['ulica'] ?? '';
		$this->data['Building number'] = $this->apiData['dane']['dzial1']['siedzibaIAdres']['adres']['nrDomu'] ?? '';
		$this->data['Office Number'] = $this->apiData['dane']['dzial1']['siedzibaIAdres']['adres']['nrLokalu'] ?? null;
		$this->data['City/Village'] = $this->apiData['dane']['dzial1']['siedzibaIAdres']['adres']['miejscowosc'] ?? $this->apiData['dane']['dzial1']['siedzibaIAdres']['siedziba']['miejscowosc'];
		$this->data['Post Code'] = $this->apiData['dane']['dzial1']['siedzibaIAdres']['adres']['kodPocztowy'] ?? '';
		$this->data['State'] = $this->apiData['dane']['dzial1']['siedzibaIAdres']['siedziba']['wojewodztwo'] ?? '';
		$this->data['Country'] = $this->apiData['dane']['dzial1']['siedzibaIAdres']['adres']['kraj'] ?? $this->apiData['dane']['dzial1']['siedzibaIAdres']['siedziba']['kraj'] ?? '';
		$this->data['Township'] = $this->apiData['dane']['dzial1']['siedzibaIAdres']['siedziba']['gmina'] ?? '';
		$this->data['County'] = $this->apiData['dane']['dzial1']['siedzibaIAdres']['siedziba']['powiat'] ?? '';
		$this->data['Web Site'] = $this->apiData['dane']['dzial1']['siedzibaIAdres']['adresStronyInternetowej'] ?? '';
		$this->data['Company'] = $this->apiData['dane']['dzial1']['danePodmiotu']['nazwa'];

		unset($this->apiData['dane']['dzial1']['siedzibaIAdres']['siedziba']['powiat'],$this->apiData['dane']['dzial1']['siedzibaIAdres']['siedziba']['gmina'],$this->apiData['dane']['dzial1']['siedzibaIAdres']['siedziba']['kraj'], $this->apiData['dane']['dzial1']['siedzibaIAdres']['adres']['kraj'],$this->apiData['dane']['dzial1']['siedzibaIAdres']['siedziba']['wojewodztwo'], $this->apiData['dane']['dzial1']['siedzibaIAdres']['adres']['kodPocztowy'], $this->apiData['dane']['dzial1']['siedzibaIAdres']['adres']['miejscowosc'], $this->apiData['dane']['dzial1']['siedzibaIAdres']['siedziba']['miejscowosc'], $this->apiData['dane']['dzial1']['siedzibaIAdres']['adres']['nrLokalu'], $this->apiData['dane']['dzial1']['siedzibaIAdres']['adres']['nrDomu'], $this->apiData['dane']['dzial1']['siedzibaIAdres']['adres']['ulica'], $this->apiData['dane']['dzial1']['danePodmiotu']['nazwa']);
	}

	/**
	 * Get Additional fields from API Polish National Court Register response.
	 *
	 * @return array
	 */
	private function getAdditional(): array
	{
		if (empty($this->apiData)) {
			return [];
		}
		$additional = [];
		foreach ($this->apiData['naglowekA'] as $key => $value) {
			$additional[$key] = $value;
		}
		foreach ($this->apiData['dane']['dzial1']['danePodmiotu'] as $key => $value) {
			if ('identyfikatory' === $key) {
				continue;
			}
			$additional[$key] = $value;
		}
		foreach ($this->apiData['dane']['dzial1']['siedzibaIAdres']['siedziba'] as $key => $value) {
			$additional[$key] = $value;
		}
		foreach ($this->apiData['dane']['dzial1']['siedzibaIAdres']['adres'] as $key => $value) {
			$additional[$key] = $value;
		}
		$additional['opisSposobuPowstaniaInformacjaOUchwale'] = $this->apiData['dane']['dzial1']['sposobPowstaniaPodmiotu']['opisSposobuPowstaniaInformacjaOUchwale'] ?? '';
		$additional['wysokoscKapitaluDocelowegoZapasowego'] = $this->apiData['dane']['dzial1']['kapital']['wysokoscKapitaluDocelowegoZapasowego']['wartosc'] . ' ' . $this->apiData['dane']['dzial1']['kapital']['wysokoscKapitaluDocelowegoZapasowego']['waluta'] ?? '';
		$additional['lacznaLiczbaAkcjiUdzialow'] = $this->apiData['dane']['dzial1']['kapital']['lacznaLiczbaAkcjiUdzialow'] ?? '';
		$additional['wartoscJednejAkcji'] = $this->apiData['dane']['dzial1']['kapital']['wartoscJednejAkcji']['wartosc'] . ' ' . $this->apiData['dane']['dzial1']['kapital']['wartoscJednejAkcji']['waluta'] ?? '';
		$additional['czescKapitaluWplaconegoPokrytego'] = $this->apiData['dane']['dzial1']['kapital']['czescKapitaluWplaconegoPokrytego']['wartosc'] . ' ' . $this->apiData['dane']['dzial1']['kapital']['czescKapitaluWplaconegoPokrytego']['waluta'] ?? '';
		if (isset($this->apiData['dane']['dzial3']['przedmiotDzialalnosci']['przedmiotPrzewazajacejDzialalnosci'][0])) {
			$mainPkd = $this->apiData['dane']['dzial3']['przedmiotDzialalnosci']['przedmiotPrzewazajacejDzialalnosci'][0];
			$additional['przedmiotPrzewazajacejDzialalnosci'] = "{$mainPkd['opis']}  ({$mainPkd['kodDzial']}.{$mainPkd['kodKlasa']}.{$mainPkd['kodPodklasa']})";
		}
		if (isset($this->apiData['dane']['dzial3']['przedmiotDzialalnosci']['przedmiotPozostalejDzialalnosci'])) {
			$info = '';
			foreach ($this->apiData['dane']['dzial3']['przedmiotDzialalnosci']['przedmiotPozostalejDzialalnosci'] as $pkd) {
				$info .= "{$pkd['opis']}  ({$pkd['kodDzial']}.{$pkd['kodKlasa']}.{$pkd['kodPodklasa']})\n";
			}
			$additional['przedmiotPozostalejDzialalnosci'] = $info;
		}
		return $additional;
	}
}
