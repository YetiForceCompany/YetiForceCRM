<?php
/**
 * YouScore from YouControl API file.
 *
 * @see https://youscore.com.ua/en/
 * @see https://youcontrol.com.ua/en/sources/
 * @see https://api.youscore.com.ua/swagger/
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
 *  YouScore from YouControl API class.
 */
class UaYouControl extends Base
{
	/** {@inheritdoc} */
	public $allowedModules = ['Accounts', 'Leads', 'Vendors', 'Partners', 'Competition'];

	/** {@inheritdoc} */
	public $icon = 'yfi-you-control';

	/** {@inheritdoc} */
	public $label = 'LBL_UA_YOU_CONTROL';

	/** {@inheritdoc} */
	public $displayType = 'FillFields';

	/** {@inheritdoc} */
	public $description = 'LBL_UA_YOU_CONTROL_DESC';

	/** {@inheritdoc} */
	public $docUrl = 'https://youscore.com.ua/en/';

	/** @var string YouScore sever address */
	private $url = 'https://api.youscore.com.ua/v1/companyInfo/';

	/** {@inheritdoc} */
	public $settingsFields = [
		'api_key' => ['required' => 1, 'purifyType' => 'Text', 'label' => 'LBL_API_KEY'],
	];

	/** @var string Api Key. */
	private $apiKey;

	/** {@inheritdoc} */
	protected $fields = [
		'companyNumber' => [
			'labelModule' => '_Base',
			'label' => 'Registration number 1',
		]
	];

	/** {@inheritdoc} */
	protected $modulesFieldsMap = [
		'Accounts' => [
			'companyNumber' => 'registration_number_1',
		],
		'Leads' => [
			'companyNumber' => 'registration_number_1',
		],
		'Vendors' => [
			'companyNumber' => 'registration_number_1',
		],
	];

	/** {@inheritdoc} */
	public $formFieldsToRecordMap = [
		'Accounts' => [
			'name' => 'accountname',
			'address' => 'addresslevel8a',
			'economicActivityCode' => 'siccode',
			'economicActivityDescription' => 'description'
		],
		'Leads' => [
			'name' => 'company',
			'address' => 'addresslevel8a',
			'economicActivityDescription' => 'description'
		],
		'Vendors' => [
			'name' => 'vendorname',
			'address' => 'addresslevel8a',
			'economicActivityDescription' => 'description'
		],
		'Partners' => [
			'name' => 'subject',
			'address' => 'addresslevel8a',
			'economicActivityDescription' => 'description'
		],
		'Competition' => [
			'name' => 'subject',
			'address' => 'addresslevel8a',
			'economicActivityDescription' => 'description'
		],
	];

	/** {@inheritdoc} */
	public function isActive(): bool
	{
		return parent::isActive() && ($params = $this->getParams()) && !empty($params['api_key']);
	}

	/** {@inheritdoc} */
	public function search(): array
	{
		$companyNumber = str_replace([' ', ',', '.', '-'], '', $this->request->getByType('companyNumber', 'Text'));
		if (!$this->isActive() || empty($companyNumber)) {
			return [];
		}
		$this->setApiKey();
		$this->getDataFromApi($companyNumber);
		$this->loadData();
		return $this->response;
	}

	/**
	 * Function fetching company data by Company Number from YouScore API.
	 *
	 * @param string $companyNumber
	 *
	 * @return void
	 */
	private function getDataFromApi(string $companyNumber): void
	{
		$response = [];
		try {
			$response = \App\RequestHttp::getClient([
				'headers' => [
					'Authorization' => 'Bearer ' . $this->apiKey
				]
			])->get($this->url . $companyNumber);
			if (200 === $response->getStatusCode()) {
				$this->data = $this->parseData(\App\Json::decode($response->getBody()->getContents()));
			}
		} catch (\GuzzleHttp\Exception\GuzzleException $e) {
			\App\Log::warning($e->getMessage(), 'RecordCollectors');
			$this->response['error'] = $e->getResponse()->getReasonPhrase();
		}
	}

	/**
	 * Function parsing data to fields from Companies House API.
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	private function parseData(array $data): array
	{
		if (empty($data)) {
			return [];
		}
		return \App\Utils::flattenKeys($data, 'ucfirst');
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
}
