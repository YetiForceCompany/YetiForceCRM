<?php
/**
 * Api Government of French Republic file.
 *
 * @see https://api.gouv.fr/les-api/api-entreprise
 * @see https://api.gouv.fr/les-api/api-recherche-entreprises
 * @see https://api.gouv.fr/documentation/api-recherche-entreprises
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
 * Api Government of French Republic class.
 */
class FrEnterpriseGouv extends Base
{
	/** {@inheritdoc} */
	public $allowedModules = ['Accounts', 'Leads', 'Vendors', 'Partners', 'Competition'];

	/** {@inheritdoc} */
	public $icon = 'yfi-entreprise-gouv-fr';

	/** {@inheritdoc} */
	public $label = 'LBL_FR_ENTERPRISE_GOUV';

	/** {@inheritdoc} */
	public $displayType = 'FillFields';

	/** {@inheritdoc} */
	public $description = 'LBL_FR_ENTERPRISE_GOUV_DESC';

	/** {@inheritdoc} */
	public $docUrl = 'https://api.gouv.fr/les-api/api-entreprise/';

	/** @var string Server address */
	private $url = 'https://recherche-entreprises.api.gouv.fr/';

	/** {@inheritdoc} */
	protected $fields = [
		'companyName' => [
			'labelModule' => '_Base',
			'label' => 'Account name',
		],
		'sicCode' => [
			'labelModule' => '_Base',
			'label' => 'SIC code',
		],
		'vatNumber' => [
			'labelModule' => '_Base',
			'label' => 'VAT',
		],
	];

	/** {@inheritdoc} */
	protected $modulesFieldsMap = [
		'Accounts' => [
			'companyName' => 'accountname',
			'sicCode' => 'siccode',
			'vatNumber' => 'vat_id'
		],
		'Leads' => [
			'companyName' => 'company',
			'vatNumber' => 'vat_id'
		],
		'Vendors' => [
			'companyName' => 'vendorname',
			'vatNumber' => 'vat_id'
		],
		'Partners' => [
			'companyName' => 'subject',
			'vatNumber' => 'vat_id'
		],
		'Competition' => [
			'companyName' => 'subject',
			'vatNumber' => 'vat_id'
		],
	];

	/** {@inheritdoc} */
	public $formFieldsToRecordMap = [
		'Accounts' => [
			'nom_complet' => 'accountname',
			'siren' => 'vat_id',
			'siegeActivite_principale' => 'siccode',
			'activite_principale' => 'siccode',
			'siegeSiret' => 'registration_number_1',
			'siegeAdresse_complete_secondaire' => 'buildingnumbera',
			'siegeAdresse_complete' => 'addresslevel8a',
			'siegeLibelle_commune' => 'addresslevel4a'
		],
		'Leads' => [
			'nom_complet' => 'company',
			'siren' => 'vat_id',
			'siegeSiret' => 'registration_number_1',
			'siegeAdresse_complete_secondaire' => 'buildingnumbera',
			'siegeAdresse_complete' => 'addresslevel8a',
			'siegeLibelle_commune' => 'addresslevel4a'
		],
		'Partners' => [
			'nom_complet' => 'subject',
			'siren' => 'vat_id',
			'siegeAdresse_complete_secondaire' => 'buildingnumbera',
			'siegeAdresse_complete' => 'addresslevel8a',
			'siegeLibelle_commune' => 'addresslevel4a'
		],
		'Vendors' => [
			'nom_complet' => 'vendorname',
			'siren' => 'vat_id',
			'siegeSiret' => 'registration_number_1',
			'siegeAdresse_complete_secondaire' => 'buildingnumbera',
			'siegeAdresse_complete' => 'addresslevel8a',
			'siegeLibelle_commune' => 'addresslevel4a'
		],
		'Competition' => [
			'nom_complet' => 'subject',
			'siren' => 'vat_id',
			'siegeAdresse_complete_secondaire' => 'buildingnumbera',
			'siegeAdresse_complete' => 'addresslevel8a',
			'siegeLibelle_commune' => 'addresslevel4a'
		],
	];

	/** @var int Number of items returned */
	const LIMIT = 4;

	/** {@inheritdoc} */
	public function search(): array
	{
		if (!$this->isActive()) {
			return [];
		}
		$vatNumber = str_replace([' ', ',', '.', '-'], '', $this->request->getByType('vatNumber', 'Text'));
		$sicCode = str_replace([' ', ',', '.', '-'], '', $this->request->getByType('sicCode', 'Text'));
		$companyName = $this->request->getByType('companyName', 'Text');
		$query = [];

		if (empty($vatNumber) && empty($sicCode) && empty($companyName)) {
			return [];
		}
		if (!empty($vatNumber)) {
			$query['q'] = $vatNumber;
		} elseif (!empty($companyName) && empty($vatNumber)) {
			$query['q'] = $companyName;
		}
		$query['per_page'] = self::LIMIT;
		if (!empty($sicCode)) {
			$query['activite_principale'] = $sicCode;
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
		try {
			$response = \App\RequestHttp::getClient()->get($this->url . 'search?' . http_build_query($query));
		} catch (\GuzzleHttp\Exception\GuzzleException $e) {
			\App\Log::warning($e->getMessage(), 'RecordCollectors');
			$this->response['error'] = $e->getMessage();
		}
		$data = isset($response) ? \App\Json::decode($response->getBody()->getContents()) : [];
		if (empty($data)) {
			return;
		}
		foreach ($data['results'] as $key => $result) {
			$this->data[$key] = $this->parseData($result);
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
