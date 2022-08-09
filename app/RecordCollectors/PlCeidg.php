<?php
/**
 * Polish Central Registration And Information On Business record collector file.
 *
 * @see https://akademia.biznes.gov.pl/hurtownia-danych/
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\RecordCollectors;

/**
 * Polish Central Registration And Information On Business record collector class.
 */
class PlCeidg extends Base
{
	/** {@inheritdoc} */
	public $allowedModules = ['Accounts', 'Leads', 'Vendors', 'Competition'];

	/** {@inheritdoc} */
	public $icon = 'yfi-cedig-pl';

	/** {@inheritdoc} */
	public $label = 'LBL_PL_CEIDG';

	/** {@inheritdoc} */
	public $displayType = 'FillFields';

	/** {@inheritdoc} */
	public $description = 'LBL_PL_CEIDG_DESC';

	/** {@inheritdoc} */
	public $docUrl = 'https://dane.biznes.gov.pl';

	/** {@inheritdoc} */
	public $settingsFields = [
		'api_key' => ['required' => 1, 'purifyType' => 'Text', 'label' => 'LBL_API_KEY'],
	];

	/** @var string Polish CEIDG sever address */
	protected $url = 'https://dane.biznes.gov.pl/api/ceidg/v2/firmy';

	/** @var string Api Key. */
	private $apiKey;

	/** {@inheritdoc} */
	protected $fields = [
		'vatId' => [
			'labelModule' => '_Base',
			'label' => 'Vat ID',
		],
		'ncr' => [
			'labelModule' => '_Base',
			'label' => 'Registration number 1',
		],
		'taxNumber' => [
			'labelModule' => '_Base',
			'label' => 'Registration number 2',
		],
		'name' => [
			'labelModule' => '_Base',
			'label' => 'LBL_COMPANY_NAME',
		],
	];

	/** {@inheritdoc} */
	protected $modulesFieldsMap = [
		'Accounts' => [
			'vatId' => 'vat_id',
			'taxNumber' => 'registration_number_2',
			'ncr' => 'registration_number_1',
		],
		'Leads' => [
			'vatId' => 'vat_id',
			'taxNumber' => 'registration_number_2',
			'ncr' => 'registration_number_1',
		],
		'Vendors' => [
			'vatId' => 'vat_id',
			'taxNumber' => 'registration_number_2',
			'ncr' => 'registration_number_1',
		],
		'Competition' => [
			'vatId' => 'vat_id',
			'taxNumber' => 'registration_number_2',
			'ncr' => 'registration_number_1',
		],
	];

	/** {@inheritdoc} */
	public $formFieldsToRecordMap = [
		'Accounts' => [
			'nazwa' => 'accountname',
			'email' => 'email1',
			'wlascicielNip' => 'vat_id',
			'wlascicielRegon' => 'registration_number_2',
			'naglowekANumerKRS' => 'registration_number_1',
			'pkdGlowny' => 'siccode',
			'adresDzialalnosciBudynek' => 'buildingnumbera',
			'adresDzialalnosciUlica' => 'addresslevel8a',
			'adresDzialalnosciKod' => 'addresslevel7a',
			'adresDzialalnosciMiasto' => 'addresslevel5a',
			'adresDzialalnosciGmina' => 'addresslevel4a',
			'adresDzialalnosciPowiat' => 'addresslevel3a',
			'adresDzialalnosciWojewodztwo' => 'addresslevel2a',
			'adresDzialalnosciKraj' => 'addresslevel1a',
			'adresKorespondencyjnyBudynek' => 'buildingnumberb',
			'adresKorespondencyjnyUlica' => 'addresslevel8b',
			'adresKorespondencyjnyKod' => 'addresslevel7b',
			'adresKorespondencyjnyMiasto' => 'addresslevel5b',
			'adresKorespondencyjnyGmina' => 'addresslevel4b',
			'adresKorespondencyjnyPowiat' => 'addresslevel3b',
			'adresKorespondencyjnyWojewodztwo' => 'addresslevel2b',
			'adresKorespondencyjnyKraj' => 'addresslevel1b'
		],
		'Leads' => [
			'nazwa' => 'company',
			'email' => 'email1',
			'wlascicielRegon' => 'registration_number_2',
			'naglowekANumerKRS' => 'registration_number_1',
			'wlascicielNip' => 'vat_id',
			'adresDzialalnosciBudynek' => 'buildingnumbera',
			'adresDzialalnosciUlica' => 'addresslevel8a',
			'adresDzialalnosciKod' => 'addresslevel7a',
			'adresDzialalnosciMiasto' => 'addresslevel5a',
			'adresDzialalnosciGmina' => 'addresslevel4a',
			'adresDzialalnosciPowiat' => 'addresslevel3a',
			'adresDzialalnosciWojewodztwo' => 'addresslevel2a',
			'adresDzialalnosciKraj' => 'addresslevel1a'
		],
		'Partners' => [
			'nazwa' => 'subject',
			'email' => 'email',
			'wlascicielNip' => 'vat_id',
			'adresDzialalnosciBudynek' => 'buildingnumbera',
			'adresDzialalnosciUlica' => 'addresslevel8a',
			'adresDzialalnosciKod' => 'addresslevel7a',
			'adresDzialalnosciMiasto' => 'addresslevel5a',
			'adresDzialalnosciGmina' => 'addresslevel4a',
			'adresDzialalnosciPowiat' => 'addresslevel3a',
			'adresDzialalnosciWojewodztwo' => 'addresslevel2a',
			'adresDzialalnosciKraj' => 'addresslevel1a'
		],
		'Vendors' => [
			'nazwa' => 'vendorname',
			'email' => 'email',
			'wlascicielRegon' => 'registration_number_2',
			'naglowekANumerKRS' => 'registration_number_1',
			'wlascicielNip' => 'vat_id',
			'adresDzialalnosciBudynek' => 'buildingnumbera',
			'adresDzialalnosciUlica' => 'addresslevel8a',
			'adresDzialalnosciKod' => 'addresslevel7a',
			'adresDzialalnosciMiasto' => 'addresslevel5a',
			'adresDzialalnosciGmina' => 'addresslevel4a',
			'adresDzialalnosciPowiat' => 'addresslevel3a',
			'adresDzialalnosciWojewodztwo' => 'addresslevel2a',
			'adresDzialalnosciKraj' => 'addresslevel1a',
			'adresKorespondencyjnyBudynek' => 'buildingnumberb',
			'adresKorespondencyjnyUlica' => 'addresslevel8b',
			'adresKorespondencyjnyKod' => 'addresslevel7b',
			'adresKorespondencyjnyMiasto' => 'addresslevel5b',
			'adresKorespondencyjnyGmina' => 'addresslevel4b',
			'adresKorespondencyjnyPowiat' => 'addresslevel3b',
			'adresKorespondencyjnyWojewodztwo' => 'addresslevel2b',
			'adresKorespondencyjnyKraj' => 'addresslevel1b'
		],
		'Competition' => [
			'nazwa' => 'subject',
			'email' => 'email',
			'wlascicielNip' => 'vat_id',
			'adresDzialalnosciBudynek' => 'buildingnumbera',
			'adresDzialalnosciUlica' => 'addresslevel8a',
			'adresDzialalnosciKod' => 'addresslevel7a',
			'adresDzialalnosciMiasto' => 'addresslevel5a',
			'adresDzialalnosciGmina' => 'addresslevel4a',
			'adresDzialalnosciPowiat' => 'addresslevel3a',
			'adresDzialalnosciWojewodztwo' => 'addresslevel2a',
			'adresDzialalnosciKraj' => 'addresslevel1a'
		]
	];

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
		$this->loadConfig();
		$this->moduleName = $this->request->getModule();
		$query = [];
		if ($vatId = str_replace([' ', ',', '.', '-'], '', $this->request->getByType('vatId', 'Text'))) {
			$query['nip'] = $vatId;
		}
		if ($taxNumber = str_replace([' ', ',', '.', '-'], '', $this->request->getByType('taxNumber', 'Text'))) {
			$query['regon'] = $taxNumber;
		}
		if ($ncr = str_replace([' ', ',', '.', '-'], '', $this->request->getByType('ncr', 'Text'))) {
			$query['nip'] = $ncr;
		}
		if ($name = $this->request->getByType('name', 'Text')) {
			$query['nazwa'] = $name;
		}
		if (!$query) {
			return [];
		}
		$this->getDataFromApi($query);
		$this->loadData();
		return $this->response;
	}

	/**
	 * Function fetching from Polish Central Registration And Information On Business API.
	 *
	 * @param array $query
	 *
	 * @return void
	 */
	private function getDataFromApi(array $query): void
	{
		$query['limit'] = self::LIMIT;
		try {
			$response = (\App\RequestHttp::getClient())->request('GET', $this->url . '?' . http_build_query($query), [
				'headers' => ['Authorization' => 'Bearer ' . $this->apiKey],
			]);
			$rows = \App\Json::decode($response->getBody()->getContents()) ?? [];
		} catch (\GuzzleHttp\Exception\GuzzleException $e) {
			\App\Log::warning($e->getMessage(), 'RecordCollectors');
			$this->response['error'] = $e->getMessage();
		}
		if (!empty($rows['firmy'])) {
			foreach ($rows['firmy'] as $key => $value) {
				try {
					$response = (\App\RequestHttp::getClient())->request('GET', $value['link'], [
						'headers' => ['Authorization' => 'Bearer ' . $this->apiKey],
					]);
					$response = \App\Json::decode($response->getBody()->getContents()) ?? [];
					if (isset($response['firma'][0])) {
						$this->data[$key] = \App\Utils::flattenKeys($this->parseData($response['firma'][0]), 'ucfirst');
					}
				} catch (\GuzzleHttp\Exception\GuzzleException $e) {
					\App\Log::warning($e->getMessage(), 'RecordCollectors');
					$this->response['error'] = $e->getMessage();
				}
			}
		} elseif (isset($query['nip'])) {
			$this->getDataFromApi(['nip_sc' => $query['nip'], 'limit' => self::LIMIT]);
		} elseif (isset($query['regon'])) {
			$this->getDataFromApi(['regon_sc' => $query['regon'], 'limit' => self::LIMIT]);
		}
	}

	/**
	 * Function setup Api Key.
	 *
	 * @return void
	 */
	private function loadConfig(): void
	{
		if (($params = $this->getParams()) && !empty($params['api_key'])) {
			$this->apiKey = $params['api_key'];
		} else {
			throw new \App\Exceptions\IllegalValue('You must fist setup Api Key in Config Panel', 403);
		}
	}

	/**
	 * Function parsing data to fields from Securities and Exchange Commission API.
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	private function parseData(array $data): array
	{
		if (isset($data['adresDzialalnosci']['kraj'])) {
			$data['adresDzialalnosci']['kraj'] = \App\Fields\Country::getCountryName($data['adresDzialalnosci']['kraj']);
		}
		if (isset($data['adresKorespondencyjny']['kraj'])) {
			$data['adresKorespondencyjny']['kraj'] = \App\Fields\Country::getCountryName($data['adresKorespondencyjny']['kraj']);
		}
		if (isset($data['pkd'])) {
			$data['pkd'] = implode(',', $data['pkd']);
		}
		unset($data['id'], $data['link']);
		return $data;
	}
}
