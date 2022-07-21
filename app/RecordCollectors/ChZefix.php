<?php
/**
 * Zefix Swiss Central Business Name Index API file.
 *
 * @package App
 *
 * @see https://www.zefix.admin.ch/
 * @see https://www.zefix.admin.ch/ZefixPublicREST/
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Rembiesa <s.rembiesa@yetiforce.com>
 */

namespace App\RecordCollectors;
/**
 * Zefix Swiss Central Business Name Index API class.
 */
class ChZefix extends Base
{
	/** {@inheritdoc} */
	protected static $allowedModules = ['Accounts', 'Leads', 'Vendors', 'Partners', 'Competition'];

	/** {@inheritdoc} */
	public $icon = 'fas fa-drum-steelpan';

	/** {@inheritdoc} */
	public $label = 'LBL_CH_ZEFIX';

	/** {@inheritdoc} */
	public $displayType = 'FillFields';

	/** {@inheritdoc} */
	public $description = 'LBL_CH_ZEFIX_DESC';

	/** {@inheritdoc} */
	public $docUrl = 'https://www.zefix.admin.ch/';

	/** {@inheritdoc} */
	protected $fields = [
		'companyId' => [
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
			'companyId' => 'registration_number_1',
			'companyName' => 'accountname'
		],
		'Leads' => [
			'companyId' => 'registration_number_1',
			'companyName' => 'company'
		],
		'Vendors' => [
			'companyId' => 'registration_number_1',
			'companyName' => 'vendorname'
		],
		'Competition' => [
			'companyId' => 'registration_number_1',
			'companyName' => 'subject'
		]
	];

	/** {@inheritdoc} */
	public $formFieldsToRecordMap = [
		'Accounts' => [
			'0Name' => 'accountname',
			'0Translation1' => 'accountname',
			'0Uid' => 'registration_number_1',
			'0CapitalNominal' => 'annual_revenue',
			'0AddressHouseNumber' => 'buildingnumbera',
			'0AddressAddon' => 'localnumbera',
			'0AddressStreet' => 'addresslevel8a',
			'0AddressPoBox' => 'poboxa',
			'0AddressSwissZipCode' => 'addresslevel7a',
			'0AddressCity' => 'addresslevel5a',
			'0Canton' => 'addresslevel2a',
			'0ZefixDetailWebEn' => 'website',
			'0Purpose' => 'description'
		],
		'Leads' => [
			'0Name' => 'company',
			'0Translation1' => 'company',
			'0Uid' => 'registration_number_1',
			'0AddressHouseNumber' => 'buildingnumbera',
			'0AddressAddon' => 'localnumbera',
			'0AddressStreet' => 'addresslevel8a',
			'0AddressPoBox' => 'poboxa',
			'0AddressSwissZipCode' => 'addresslevel7a',
			'0AddressCity' => 'addresslevel5a',
			'0Canton' => 'addresslevel2a',
			'0ZefixDetailWebEn' => 'website',
			'0Purpose' => 'description'
		],
		'Partners' => [
			'0Name' => 'subject',
			'0Translation1' => 'subject',
			'0AddressHouseNumber' => 'buildingnumbera',
			'0AddressAddon' => 'localnumbera',
			'0AddressStreet' => 'addresslevel8a',
			'0AddressPoBox' => 'poboxa',
			'0AddressSwissZipCode' => 'addresslevel7a',
			'0AddressCity' => 'addresslevel5a',
			'0Canton' => 'addresslevel2a',
			'0Purpose' => 'description'
		],
		'Vendors' => [
			'0Name' => 'vendorname',
			'0Translation1' => 'vendorname',
			'0Uid' => 'registration_number_1',
			'0AddressHouseNumber' => 'buildingnumbera',
			'0AddressAddon' => 'localnumbera',
			'0AddressStreet' => 'addresslevel8a',
			'0AddressPoBox' => 'poboxa',
			'0AddressSwissZipCode' => 'addresslevel7a',
			'0AddressCity' => 'addresslevel5a',
			'0Canton' => 'addresslevel2a',
			'0ZefixDetailWebEn' => 'website',
			'0Purpose' => 'description'
		],
		'Competition' => [
			'0Name' => 'subject',
			'0Translation1' => 'subject',
			'0AddressHouseNumber' => 'buildingnumbera',
			'0AddressAddon' => 'localnumbera',
			'0AddressStreet' => 'addresslevel8a',
			'0AddressPoBox' => 'poboxa',
			'0AddressSwissZipCode' => 'addresslevel7a',
			'0AddressCity' => 'addresslevel5a',
			'0Canton' => 'addresslevel2a',
			'0Purpose' => 'description'
		],
	];

	/** {@inheritdoc} */
	private $url = 'https://www.zefix.admin.ch/ZefixPublicREST/api/v1/company/';

	/** {@inheritdoc} */
	public $settingsFields = [
		'username' => ['required' => 1, 'purifyType' => 'Text', 'label' => 'Username'],
		'password' => ['required' => 1, 'purifyType' => 'Text', 'label' => 'Password'],
	];

	/** @var string Username. */
	private $username;
	/** @var string Password. */
	private $password;

	/** {@inheritdoc} */
	public function isActive(): bool
	{
		return parent::isActive() && ($params = $this->getParams()) && !empty($params['username'] && !empty($params['password']));
	}

	/** {@inheritdoc} */
	public function search(): array
	{
		$companyId = str_replace([' ', ',', '.', '-'], '', $this->request->getByType('companyId', 'Text'));
		$companyName = $this->request->getByType('companyName', 'Text');

		if (!$this->isActive() || (empty($companyId) && empty($companyName))) {
			return [];
		}

		$this->loadCredentials();
		if (!empty($companyId)) {
			$this->getCompanyById($companyId);
		} elseif ((empty($companyId) || empty($this->data)) && !empty($companyName)){
			$this->getCompaniesByName($companyName);
		} else {
			return [];
		}

		$this->loadData();
		return $this->response;
	}

	/**
	 * Function setup Credentials.
	 *
	 * @return void
	 */
	private function loadCredentials(): void
	{
		if (($params = $this->getParams()) && !empty($params['username'] && !empty($params['password']))) {
			$this->username = $params['username'];
			$this->password = $params['password'];
		}
	}

	/**
	 * Function parsing data to fields from API.
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	private function parseData(array $data): array
	{
		return \App\Utils::flattenKeys($data, 'ucfirst');
	}

	/**
	 * Function fetching company data by Company ID.
	 *
	 * @param string $companyId
	 *
	 * @return void
	 */
	private function getCompanyById(string $companyId): void
	{
		$options = ['auth' => [$this->username , $this->password]];

		try {
			$response = \App\RequestHttp::getClient()->get($this->url . 'uid/' . $companyId, $options);
			if (200 === $response->getStatusCode()) {
				$this->data = $this->parseData(\App\Json::decode($response->getBody()->getContents()));
			}
		} catch (\GuzzleHttp\Exception\ClientException $e) {
			\App\Log::warning($e->getMessage(), 'RecordCollectors');
			$this->response['error'] = $e->getResponse()->getReasonPhrase();
			var_dump($e->getResponse()->getReasonPhrase()); //remove after dev
		}
	}
	/**
	 * Function fetching companies data by company name.
	 *
	 * @param string $companyName
	 * @return void
	 */
	private function getCompaniesByName(string $companyName): void
	{
		$options = [
			'auth' => [$this->username , $this->password],
			'body' => \App\Json::encode(['name' => $companyName]),
			'headers' => [
				'Accept' => 'application/json',
				'Content-Type' => 'application/json'
			],
		];

		try {
			$response = \App\RequestHttp::getClient()->post($this->url . 'search', $options);
			if (200 === $response->getStatusCode()) {
				// $this->data = $this->parseData(\App\Json::decode($response->getBody()->getContents()));
				echo '<pre>';
				print_r(\App\Json::decode($response->getBody()->getContents()));
				echo '</pre>';
			}
		} catch (\GuzzleHttp\Exception\ClientException $e) {
			\App\Log::warning($e->getMessage(), 'RecordCollectors');
			$this->response['error'] = $e->getResponse()->getReasonPhrase();
			var_dump($e->getResponse()->getReasonPhrase()); //remove after dev
		}
	}
}
