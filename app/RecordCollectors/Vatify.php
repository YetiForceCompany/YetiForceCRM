<?php
/**
 * Vatify API file.
 *
 * @package App
 *
 * @see https://www.vatify.eu/docs/api/getting-started/
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Rembiesa <s.rembiesa@yetiforce.com>
 */

namespace App\RecordCollectors;
/**
 * Vatify API class.
 */
class Vatify extends Base
{
	/** {@inheritdoc} */
	protected static $allowedModules = ['Accounts', 'Leads', 'Vendors', 'Partners', 'Competition'];

	/** {@inheritdoc} */
	public $icon = 'fas fa-globe-europe';

	/** {@inheritdoc} */
	public $label = 'LBL_VATIFY';

	/** {@inheritdoc} */
	public $displayType = 'FillFields';

	/** {@inheritdoc} */
	public $description = 'LBL_VATIFY_DESC';

	/** {@inheritdoc} */
	public $docUrl = 'https://www.vatify.eu/docs/api/getting-started/';

	/** {@inheritdoc} */
	protected $fields = [
		'country' => [
			'labelModule' => '_Base',
			'label' => 'Country',
			'typeofdata' => 'V~M',
			'uitype' => 35
		],
		'vatNumber' => [
			'labelModule' => '_Base',
			'label' => 'Vat ID',
			'typeofdata' => 'V~M',
		]
	];

	/** {@inheritdoc} */
	protected $modulesFieldsMap = [
		'Accounts' => [
			'vatNumber' => 'vat_id',
			'country' => 'addresslevel1a'
		],
		'Leads' => [
			'vatNumber' => 'vat_id',
			'country' => 'addresslevel1a'
		],
		'Vendors' => [
			'vatNumber' => 'vat_id',
			'country' => 'addresslevel1a'
		],
		'Competition' => [
			'vatNumber' => 'vat_id',
			'country' => 'addresslevel1a'
		],
	];

	/** {@inheritdoc} */
	public $formFieldsToRecordMap = [];

	/** {@inheritdoc} */
	private $url = 'https://api.vatify.eu/v1/demo/query?';

	/** {@inheritdoc} */
	public function search(): array
	{
		$country = $this->request->getByType('country', 'Text');
		$vatNumber = str_replace([' ', ',', '.', '-'], '', $this->request->getByType('vatNumber', 'Text'));
		$params = [];

		if (!$this->isActive() && empty($country) && empty($vatNumber)) {
			return [];
		}

		$params['country'] = $country;
		$params['identifier'] = $vatNumber;
		$this->getDataFromApi($params);

		return [];
	}
	/**
	 * Function fetching company data by VAT ID and Country.
	 *
	 * @param array $params
	 * @return void
	 */
	private function getDataFromApi(array $params): void
	{
		$response = [];
		$link = '';
		try {
			$response = \App\Json::decode(\App\RequestHttp::getClient()->get($this->url . http_build_query($params))->getBody()->getContents());
		} catch (\GuzzleHttp\Exception\ClientException $e) {
			\App\Log::warning($e->getMessage(), 'RecordCollectors');
			$this->response['error'] = $e->getMessage();
			var_dump($e->getMessage()); //to remove after development
			return;
		}

		if ('IN_PROGRESS' === $response['query']['status']) {
			$link = $response['query']['links'][0]['href'];
		}
		//todo
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
