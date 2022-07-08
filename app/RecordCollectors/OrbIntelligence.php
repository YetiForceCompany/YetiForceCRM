<?php
/**
 * Orb Intelligence API by The Dun & Bradstreet file.
 *
 * @package App
 *
 * @see https://api.orb-intelligence.com/docs/
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Rembiesa <s.rembiesa@yetiforce.com>
 */

namespace App\RecordCollectors;

/**
 * Orb Intelligence by The Dun & Bradstreet class.
 */
class OrbIntelligence extends Base
{
	/** {@inheritdoc} */
	protected static $allowedModules = ['Accounts', 'Leads', 'Partners', 'Vendors', 'Competition'];

	/** {@inheritdoc} */
	public $icon = 'fa-solid fa-earth-americas';

	/** {@inheritdoc} */
	public $label = 'LBL_ORB';

	/** {@inheritdoc} */
	public $displayType = 'FillFields';

	/** {@inheritdoc} */
	public $description = 'LBL_ORB_DESC';

	/** {@inheritdoc} */
	public $docUrl = 'https://api.orb-intelligence.com/';

	/** {@inheritdoc} */
	protected $fields = [
		'country' => [
			'labelModule' => '_Base',
			'label' => 'Country',
			'typeofdata' => 'V~M',
		],
		'name' => [
			'labelModule' => '_Base',
			'label' => 'Account Name',
			'typeofdata' => 'V~O',
		],
		'vatNumber' => [
			'labelModule' => '_Base',
			'label' => 'VAT',
			'typeofdata' => 'V~O',
		]
	];

	/** {@inheritdoc} */
	protected $modulesFieldsMap = [
		'Accounts' => [
			'name' => 'accountname',
			'country' => 'addresslevel1a',
			'vatNumber' => 'vat_id'
		],
		'Leads' => [
			'country' => 'addresslevel1a',
			'name' => 'company',
			'vatNumber' => 'vat_id'
		],
		'Vendors' => [
			'country' => 'addresslevel1a',
			'name' => 'vendorname',
			'vatNumber' => 'vat_id'
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
			'addressAddress2' => 'buildingnumbera',
			'addressAddress1' => 'addresslevel8a',
			'addressZip' => 'addresslevel7a',
			'addressCity' => 'addresslevel5a',
			'addressState' => 'addresslevel2a',
			'addressCountry' => 'addresslevel1a'
		],
		'Leads' => [
		],
		'Partners' => [
		],
		'Vendors' => [
		],
		'Competition' => [
		]
	];

	/** {@inheritdoc} */
	public $settingsFields = [
		'api_key' => ['required' => 1, 'purifyType' => 'Text', 'label' => 'LBL_API_KEY']
	];

	/** @var string Api Key. */
	private $apiKey;

	/** @var int Limit for fetching companies */
	const LIMIT = 4;

	/** @var string ORB Intelligence sever address */
	protected $url = 'https://api.orb-intelligence.com/';

	/** @var string[] USA match names */
	private $usaMatchNames = ['us', 'usa', 'united states', 'united states of america'];

	/** {@inheritdoc} */
	public function search(): array
	{
		if (!$this->isActive()) {
			return [];
		}
		$this->setApiKey();
		$query['api_key'] = $this->apiKey;

		$country = str_replace([' ', ',', '.', '-'], '', $this->request->getByType('country', 'Text'));
		$vatNumber = str_replace([' ', ',', '.', '-'], '', $this->request->getByType('vatNumber', 'Text'));
		$name = $this->request->getByType('name', 'Text');

		if ($country && \in_array(strtolower($country), $this->usaMatchNames) && !empty($vatNumber)) {
			$query['ein'] = $vatNumber;
		} elseif (!empty($country) && !empty($name)) {
			$query['name'] = $name;
		} else {
			return [];
		}

		$query['country'] = $country;
		$this->getDataFromApi($query);
		$this->loadData();
		return $this->response;
	}

	/**
	 * Function fetching company data by params.
	 *
	 * @param string $country
	 * @param string $fieldName
	 * @param string $fieldValue
	 * @param array  $query
	 *
	 * @return void
	 */
	private function getDataFromApi(array $query): void
	{
		$response = [];
		try {
			$response = \App\RequestHttp::getClient()->get($this->url . '3/match/?' . http_build_query($query), []);
		} catch (\GuzzleHttp\Exception\ClientException $e) {
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
				$response = \App\RequestHttp::getClient()->get($result['fetch_url'], []);
			} catch (\GuzzleHttp\Exception\ClientException $e) {
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
	private function setApiKey(): void
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
