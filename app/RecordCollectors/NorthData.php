<?php
/**
 *  NorthData file.
 *
 * @package App
 *
 * @see https://www.northdata.com/_coverage
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Rembiesa <s.rembiesa@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\RecordCollectors;

/**
 *  NorthData class.
 */
class NorthData extends Base
{
	/** {@inheritdoc} */
	protected static $allowedModules = ['Accounts', 'Leads', 'Vendors', 'Partners', 'Competition'];

	/** {@inheritdoc} */
	public $icon = 'fas fa-mountain';

	/** {@inheritdoc} */
	public $label = 'LBL_NORTH_DATA';

	/** {@inheritdoc} */
	public $displayType = 'FillFields';

	/** {@inheritdoc} */
	public $description = 'LBL_NORTH_DATA_DESC';

	/** {@inheritdoc} */
	public $docUrl = 'https://www.northdata.com/_coverage';

	/** {@inheritdoc} */
	protected $fields = [
		'companyName' => [
			'labelModule' => '_Base',
			'label' => 'Account Name',
		],
		'address' => [
			'labelModule' => '_Base',
			'label' => 'Street',
		],
		'city' => [
			'labelModule' => '_Base',
			'label' => 'City',
		],
		'ncr' => [
			'labelModule' => '_Base',
			'label' => 'Registration number 1',
		],
	];

	/** {@inheritdoc} */
	protected $modulesFieldsMap = [
		'Accounts' => [
			'companyName' => 'accountname',
			'address' => 'addresslevel8a',
			'city' => 'addresslevel5a',
			'ncr' => 'registration_number_1'
		],
		'Leads' => [
			'companyName' => 'company',
			'address' => 'addresslevel8a',
			'city' => 'addresslevel5a',
			'ncr' => 'registration_number_1'
		],
	];

	/** {@inheritdoc} */
	public $formFieldsToRecordMap = [
		'Accounts' => [
			'nameName' => 'accountname',
			'registerId' => 'registration_number_1',
			'capitalItems0Value' => 'annual_revenue',
			'segmentCodesIsic0' => 'siccode',
			'addressStreet' => 'addresslevel8a',
			'addressPostalCode' => 'addresslevel7a',
			'addressCity' => 'addresslevel5a',
			'addressState' => 'addresslevel2a',
			'addressCountry' => 'addresslevel1a',
		],
		'Leads' => [
			'nameName' => 'company',
			'registerId' => 'registration_number_1',
			'addressStreet' => 'addresslevel8a',
			'addressPostalCode' => 'addresslevel7a',
			'addressCity' => 'addresslevel5a',
			'addressState' => 'addresslevel2a',
			'addressCountry' => 'addresslevel1a',
		],
		'Partners' => [
			'nameName' => 'subject',
			'addressStreet' => 'addresslevel8a',
			'addressPostalCode' => 'addresslevel7a',
			'addressCity' => 'addresslevel5a',
			'addressState' => 'addresslevel2a',
			'addressCountry' => 'addresslevel1a',
		],
		'Vendors' => [
			'nameName' => 'vendorname',
			'registerId' => 'registration_number_1',
			'addressStreet' => 'addresslevel8a',
			'addressPostalCode' => 'addresslevel7a',
			'addressCity' => 'addresslevel5a',
			'addressState' => 'addresslevel2a',
			'addressCountry' => 'addresslevel1a',
		],
		'Competition' => [
			'nameName' => 'subject',
			'addressStreet' => 'addresslevel8a',
			'addressPostalCode' => 'addresslevel7a',
			'addressCity' => 'addresslevel5a',
			'addressState' => 'addresslevel2a',
			'addressCountry' => 'addresslevel1a',
		],
	];

	/** {@inheritdoc} */
	public $settingsFields = [
		'api_key' => ['required' => 1, 'purifyType' => 'Text', 'label' => 'LBL_API_KEY']
	];

	/** @var string Api Key. */
	private $apiKey;

	/** @var string NorthData sever address */
	protected $url = 'https://www.northdata.com/_api/company/v1/';

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
		$companyName =  $this->request->getByType('companyName', 'Text');
		$address =  $this->request->getByType('address', 'Text');
		$city =  $this->request->getByType('city', 'Text');
		$ncr =  str_replace([' ', '/', '.', '-'], '', $this->request->getByType('ncr', 'Text'));

		$params = [];
		if (!empty($companyName) || !empty($address) || !empty($city) || !empty($ncr)) {
			if (!empty($companyName)) {
				$params['name'] = $companyName;
			}
			if (!empty($address)) {
				$params['address'] = $address;
			}
			if (!empty($city)) {
				$params['registerCity'] = $city;
			}
			if (!empty($ncr)) {
				$params['registerId'] = $ncr;
			}
		} else {
			return [];
		}

		$this->getDataFromApi($params);
		$this->loadData();
		return $this->response;
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
	 * Function fetching company data by params.
	 *
	 * @param array $query
	 *
	 * @return void
	 */
	private function getDataFromApi(array $params): void
	{
		$response = [];
		$options = ['headers' => [
			'X-Api-Key' => $this->apiKey
		]];
		try {
			$response = \App\RequestHttp::getClient()->get($this->url . 'company?' . http_build_query($params), $options)->getBody()->getContents();
			if ('string' === gettype($response)) {
				$response = \App\Json::decode($response);
			}
		} catch (\GuzzleHttp\Exception\ClientException $e) {
			\App\Log::warning($e->getMessage(), 'RecordCollectors');
			$this->response['error'] = $e->getResponse()->getReasonPhrase();
		}
		$this->data = $this->parseData($response);
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
