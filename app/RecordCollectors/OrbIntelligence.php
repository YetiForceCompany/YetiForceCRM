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
			'results0Name' => 'accountname',
			'results0Match_maskEin' => 'vat_id',
			'results0Match_maskWebdomain' => 'website',
			'results0Match_maskPhone' => 'phone',
			'results0Address1' => 'addresslevel8a',
			'results0Zip' => 'addresslevel7a',
			'results0City' => 'addresslevel5a',
			'results0State' => 'addresslevel2a',
			'results0Country' => 'addresslevel1a'
		],
		'Leads' => [
			'results0Name' => 'company',
			'results0Match_maskEin' => 'vat_id',
			'results0Match_maskWebdomain' => 'website',
			'results0Match_maskPhone' => 'phone',
			'results0Address1' => 'addresslevel8a',
			'results0Zip' => 'addresslevel7a',
			'results0City' => 'addresslevel5a',
			'results0State' => 'addresslevel2a',
			'results0Country' => 'addresslevel1a'
		],
		'Partners' => [
			'results0Name' => 'subject',
			'results0Match_maskEin' => 'vat_id',
			'results0Address1' => 'addresslevel8a',
			'results0Zip' => 'addresslevel7a',
			'results0City' => 'addresslevel5a',
			'results0State' => 'addresslevel2a',
			'results0Country' => 'addresslevel1a'
		],
		'Vendors' => [
			'results0Name' => 'vendorname',
			'results0Match_maskEin' => 'vat_id',
			'results0Match_maskWebdomain' => 'website',
			'results0Match_maskPhone' => 'phone',
			'results0Address1' => 'addresslevel8a',
			'results0Zip' => 'addresslevel7a',
			'results0City' => 'addresslevel5a',
			'results0State' => 'addresslevel2a',
			'results0Country' => 'addresslevel1a'
		],
		'Competition' => [
			'results0Name' => 'subject',
			'results0Match_maskEin' => 'vat_id',
			'results0Address1' => 'addresslevel8a',
			'results0Zip' => 'addresslevel7a',
			'results0City' => 'addresslevel5a',
			'results0State' => 'addresslevel2a',
			'results0Country' => 'addresslevel1a'
		]
	];

	/** {@inheritdoc} */
	public $settingsFields = [
		'api_key' => ['required' => 1, 'purifyType' => 'Text', 'label' => 'LBL_API_KEY']
	];

	/** @var string Api Key. */
	private $apiKey;

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
		$fieldName = '';
		$fieldValue = '';
		$country = str_replace([' ', ',', '.', '-'], '', $this->request->getByType('country', 'Text'));
		$vatNumber = str_replace([' ', ',', '.', '-'], '', $this->request->getByType('vatNumber', 'Text'));
		$name = $this->request->getByType('name', 'Text');

		if ($country && \in_array(strtolower($country), $this->usaMatchNames) && !empty($vatNumber)) {
			$fieldName = 'ein';
			$fieldValue = $vatNumber;
		} elseif (!empty($country) && !empty($name)) {
			$fieldName = 'name';
			$fieldValue = $name;
		} else {
			return [];
		}

		$this->getDataFromApi($country, $fieldName, $fieldValue);
		$this->parseData();
		$this->loadData();
		return $this->response;
	}

	/**
	 * Function fetching company data by params.
	 *
	 * @param string $country
	 * @param string $fieldName
	 * @param string $fieldValue
	 *
	 * @return void
	 */
	private function getDataFromApi(string $country, string $fieldName, string $fieldValue): void
	{
		$response = [];
		try {
			$response = \App\RequestHttp::getClient()->get($this->url . '3/match/?api_key=' . $this->apiKey . "&{$fieldName}=" . $fieldValue . '&country=' . $country, []);
		} catch (\GuzzleHttp\Exception\ClientException $e) {
			\App\Log::warning($e->getMessage(), 'RecordCollectors');
			$this->response['error'] = $e->getMessage();
			var_dump($e->getMessage());
		}
		$this->data = isset($response) ? \App\Json::decode($response->getBody()->getContents()) : [];
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
	 * @return void
	 */
	private function parseData(): void
	{
		if (empty($this->data)) {
			return;
		}
		$this->data = \App\Utils::flattenKeys($this->data, 'ucfirst');
	}
}
