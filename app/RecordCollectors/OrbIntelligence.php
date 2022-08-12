<?php
/**
 * Orb Intelligence API by The Dun & Bradstreet file.
 *
 * @see https://api.orb-intelligence.com/docs/
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Rembiesa <s.rembiesa@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\RecordCollectors;

/**
 * Orb Intelligence by The Dun & Bradstreet class.
 */
class OrbIntelligence extends Base
{
	/** {@inheritdoc} */
	public $allowedModules = ['Accounts', 'Leads', 'Partners', 'Vendors', 'Competition'];

	/** {@inheritdoc} */
	public $icon = 'yfi-orb';

	/** {@inheritdoc} */
	public $label = 'LBL_ORB';

	/** {@inheritdoc} */
	public $displayType = 'FillFields';

	/** {@inheritdoc} */
	public $description = 'LBL_ORB_DESC';

	/** {@inheritdoc} */
	public $docUrl = 'https://api.orb-intelligence.com/';

	/** {@inheritdoc} */
	public $settingsFields = [
		'api_key' => ['required' => 1, 'purifyType' => 'Text', 'label' => 'LBL_API_KEY']
	];

	/** @var string ORB Intelligence sever address */
	protected $url = 'https://api.orb-intelligence.com/';

	/** @var string Api Key. */
	private $apiKey;

	/** {@inheritdoc} */
	protected $fields = [
		'country' => [
			'labelModule' => '_Base',
			'label' => 'Country',
			'typeofdata' => 'V~M',
			'uitype' => 35
		],
		'name' => [
			'labelModule' => '_Base',
			'label' => 'Account Name',
		],
		'vatNumber' => [
			'labelModule' => 'Other.RecordCollector',
			'label' => 'LBL_ORB_VAT',
		],
		'phone' => [
			'labelModule' => '_Base',
			'label' => 'Phone',
		],
		'email' => [
			'labelModule' => '_Base',
			'label' => 'Email',
		]
	];

	/** {@inheritdoc} */
	protected $modulesFieldsMap = [
		'Accounts' => [
			'name' => 'accountname',
			'country' => 'addresslevel1a',
			'vatNumber' => 'vat_id',
			'email' => 'email1',
			'phone' => 'phone'
		],
		'Leads' => [
			'country' => 'addresslevel1a',
			'name' => 'company',
			'vatNumber' => 'vat_id',
			'email' => 'email',
			'phone' => 'phone',
			'email' => 'email',
			'phone' => 'phone'
		],
		'Vendors' => [
			'country' => 'addresslevel1a',
			'name' => 'vendorname',
			'vatNumber' => 'vat_id',
			'email' => 'email',
			'phone' => 'phone'
		],
		'Partners' => [
			'country' => 'addresslevel1a',
			'name' => 'subject',
			'vatNumber' => 'vat_id',
			'email' => 'email',
			'phone' => 'phone'
		],
		'Competition' => [
			'country' => 'addresslevel1a',
			'name' => 'subject',
			'vatNumber' => 'vat_id',
			'email' => 'email',
			'phone' => 'phone'
		],
	];

	/** {@inheritdoc} */
	public $formFieldsToRecordMap = [
		'Accounts' => [
			'name' => 'accountname',
			'eins0' => 'vat_id',
			'website' => 'website',
			'email' => 'email1',
			'phone' => 'phone',
			'sic_code' => 'siccode',
			'cik' => 'registration_number_1',
			'description' => 'description',
			'addressAddress2' => 'buildingnumbera',
			'addressAddress1' => 'addresslevel8a',
			'addressZip' => 'addresslevel7a',
			'addressCity' => 'addresslevel5a',
			'addressState' => 'addresslevel2a',
			'addressCountry' => 'addresslevel1a'
		],
		'Leads' => [
			'name' => 'company',
			'eins0' => 'vat_id',
			'website' => 'website',
			'email' => 'email',
			'phone' => 'phone',
			'cik' => 'registration_number_1',
			'description' => 'description',
			'addressAddress2' => 'buildingnumbera',
			'addressAddress1' => 'addresslevel8a',
			'addressZip' => 'addresslevel7a',
			'addressCity' => 'addresslevel5a',
			'addressState' => 'addresslevel2a',
			'addressCountry' => 'addresslevel1a'
		],
		'Partners' => [
			'name' => 'subject',
			'eins0' => 'vat_id',
			'email' => 'email',
			'sic_code' => 'siccode',
			'cik' => 'registration_number_1',
			'description' => 'description',
			'addressAddress2' => 'buildingnumbera',
			'addressAddress1' => 'addresslevel8a',
			'addressZip' => 'addresslevel7a',
			'addressCity' => 'addresslevel5a',
			'addressState' => 'addresslevel2a',
			'addressCountry' => 'addresslevel1a'
		],
		'Vendors' => [
			'name' => 'vendorname',
			'eins0' => 'vat_id',
			'website' => 'website',
			'email' => 'email',
			'phone' => 'phone',
			'cik' => 'registration_number_1',
			'description' => 'description',
			'addressAddress2' => 'buildingnumbera',
			'addressAddress1' => 'addresslevel8a',
			'addressZip' => 'addresslevel7a',
			'addressCity' => 'addresslevel5a',
			'addressState' => 'addresslevel2a',
			'addressCountry' => 'addresslevel1a'
		],
		'Competition' => [
			'name' => 'subject',
			'eins0' => 'vat_id',
			'email' => 'email',
			'cik' => 'registration_number_1',
			'description' => 'description',
			'addressAddress2' => 'buildingnumbera',
			'addressAddress1' => 'addresslevel8a',
			'addressZip' => 'addresslevel7a',
			'addressCity' => 'addresslevel5a',
			'addressState' => 'addresslevel2a',
			'addressCountry' => 'addresslevel1a'
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
		$query['api_key'] = $this->apiKey;

		$country = $this->request->getByType('country', 'Text');
		$vatNumber = str_replace([' ', ',', '.', '-'], '', $this->request->getByType('vatNumber', 'Text'));
		$name = $this->request->getByType('name', 'Text');
		$mail = $this->request->getByType('email', 'Text');
		$phone = $this->request->getByType('phone', 'Text');

		if (!$country) {
			return [];
		}
		$query['country'] = $country;
		if ('United States' === $country && !empty($vatNumber)) {
			$query['ein'] = $vatNumber;
		} elseif (!empty($name)) {
			$query['name'] = $name;
		} elseif (empty($name) && !empty($mail)) {
			$query['email'] = $mail;
		} elseif (empty($name) && !empty($phone)) {
			$query['phone'] = $phone;
		}

		$this->getDataFromApi($query);
		$this->loadData();
		return $this->response;
	}

	/**
	 * Function fetching company data by params.
	 *
	 * @param array $query
	 *
	 * @return void
	 */
	private function getDataFromApi(array $query): void
	{
		$response = [];
		$client = \App\RequestHttp::getClient(['timeout' => 60]);
		try {
			$response = $client->get($this->url . '3/match/?' . http_build_query($query));
		} catch (\GuzzleHttp\Exception\GuzzleException $e) {
			\App\Log::warning($e->getMessage(), 'RecordCollectors');
			$this->response['error'] = $e->getMessage();
		}
		$data = isset($response) ? \App\Json::decode($response->getBody()->getContents()) : [];
		if (empty($data)) {
			return;
		}
		$data = \array_slice($data['results'], 0, self::LIMIT, true);
		foreach ($data as $key => $result) {
			try {
				$response = $client->get($result['fetch_url']);
			} catch (\GuzzleHttp\Exception\GuzzleException $e) {
				\App\Log::warning($e->getMessage(), 'RecordCollectors');
				$this->response['error'] = $e->getMessage();
			}
			$this->data[$key] = $this->parseData(\App\Json::decode($response->getBody()->getContents()));
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
	 * Function parsing data to fields from ORB Intelligence API.
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	private function parseData(array $data): array
	{
		return \App\Utils::flattenKeys($data, 'ucfirst');
	}
}
