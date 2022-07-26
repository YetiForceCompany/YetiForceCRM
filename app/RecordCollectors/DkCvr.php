<?php
/**
 * The Danish Central Business Register (CVR) file.
 *
 * @package App
 *
 * @see
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Rembiesa <s.rembiesa@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\RecordCollectors;

/**
 * The Danish Central Business Register (CVR) class.
 */
class DkCvr extends Base
{
	/** {@inheritdoc} */
	protected static $allowedModules = ['Accounts', 'Leads', 'Vendors', 'Partners', 'Competition'];

	/** {@inheritdoc} */
	public $icon = 'fas fa-city';

	/** {@inheritdoc} */
	public $label = 'LBL_DK_CVR';

	/** {@inheritdoc} */
	public $displayType = 'FillFields';

	/** {@inheritdoc} */
	public $description = 'LBL_DK_CVR_DESC';

	/** {@inheritdoc} */
	public $docUrl = 'https://cvrapi.dk/';

	/** @var string CH sever address */
	private $url = 'http://cvrapi.dk/api?';

	/** {@inheritdoc} */
	protected $fields = [
		'country' => [
			'labelModule' => '_Base',
			'label' => 'Country',
			'picklistModule' => 'Other.Country',
			'typeofdata' => 'V~M',
			'uitype' => 16,
			'picklistValues' => [
				'no' => 'Norway',
				'dk' => 'Denmark'
			]
		],
		'vatNumber' => [
			'labelModule' => '_Base',
			'label' => 'Vat ID',
		]
	];

	/** {@inheritdoc} */
	protected $modulesFieldsMap = [
		'Accounts' => [
			'vatNumber' => 'vat_id',
		],
		'Leads' => [
			'vatNumber' => 'vat_id',
		],
		'Vendors' => [
			'vatNumber' => 'vat_id',
		],
		'Competition' => [
			'vatNumber' => 'vat_id',
		]
	];

	/** {@inheritdoc} */
	public $formFieldsToRecordMap = [
		'Accounts' => [
			'name' => 'accountname',
			'vat' => 'vat_id',
			'address' => 'addresslevel8a',
			'zipcode' => 'addresslevel7a',
			'city' => 'addresslevel5a',
			'phone' => 'phone',
			'fax' => 'fax',
			'email' => 'email1',
			'industrycode' => 'siccode',
			'description' => 'industrydesc'
		],
		'Leads' => [
			'name' => 'company',
			'vat' => 'vat_id',
			'address' => 'addresslevel8a',
			'zipcode' => 'addresslevel7a',
			'city' => 'addresslevel5a',
			'phone' => 'phone',
			'fax' => 'fax',
			'email' => 'email',
			'description' => 'industrydesc'
		],
		'Vendors' => [
			'name' => 'vendorname',
			'vat' => 'vat_id',
			'address' => 'addresslevel8a',
			'zipcode' => 'addresslevel7a',
			'city' => 'addresslevel5a',
			'phone' => 'phone',
			'fax' => 'fax',
			'email' => 'email',
			'description' => 'industrydesc'
		],
		'Partners' => [
			'name' => 'subject',
			'vat' => 'vat_id',
			'address' => 'addresslevel8a',
			'zipcode' => 'addresslevel7a',
			'city' => 'addresslevel5a',
			'phone' => 'phone',
			'fax' => 'fax',
			'email' => 'email',
			'description' => 'industrydesc'
		],
		'Competition' => [
			'name' => 'subject',
			'vat' => 'vat_id',
			'address' => 'addresslevel8a',
			'zipcode' => 'addresslevel7a',
			'city' => 'addresslevel5a',
			'phone' => 'phone',
			'fax' => 'fax',
			'email' => 'email',
			'description' => 'industrydesc'
		]
	];

	/** {@inheritdoc} */
	public function search(): array
	{
		$country = $this->request->getByType('country', 'Text');
		$vatNumber = str_replace([' ', ',', '.', '-'], '', $this->request->getByType('vatNumber', 'Text'));
		$params = [];
		if (!$this->isActive() && empty($country) && empty($vatNumber)) {
			return [];
		}
		$params['search'] = $vatNumber;
		$params['country'] = $country;

		$this->getDataFromApi($params);
		$this->loadData();
		return $this->response;
	}

	/**
	 * Function fetching company data by Company Number (CVR).
	 *
	 * @param array $params
	 *
	 * @return void
	 */
	private function getDataFromApi($params): void
	{
		$response = [];
		try {
			$response = \App\RequestHttp::getClient()->get($this->url . http_build_query($params));
			if (200 === $response->getStatusCode()) {
				$this->data = $this->parseData(\App\Json::decode($response->getBody()->getContents()));
			}
		} catch (\GuzzleHttp\Exception\ClientException $e) {
			\App\Log::warning($e->getMessage(), 'RecordCollectors');
			$this->response['error'] = $e->getResponse()->getReasonPhrase();
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
}
