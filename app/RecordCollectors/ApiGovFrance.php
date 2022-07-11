<?php
/**
 * Api Government of French Republic file.
 *
 * @package App
 *
 * @see https://api.gouv.fr/les-api/api-entreprise
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Rembiesa <s.rembiesa@yetiforce.com>
 */

namespace App\RecordCollectors;

/**
 * Api Government of French Republic class.
 */
class ApiGovFrance extends Base
{
	/** {@inheritdoc} */
	protected static $allowedModules = ['Accounts', 'Leads', 'Vendors', 'Partners', 'Competition'];

	/** {@inheritdoc} */
	public $icon = 'fas fa-flag';

	/** {@inheritdoc} */
	public $label = 'LBL_API_GOV_FRANCE';

	/** {@inheritdoc} */
	public $displayType = 'FillFields';

	/** {@inheritdoc} */
	public $description = 'LBL_API_GOV_FRANCE_DESC';

	/** {@inheritdoc} */
	public $docUrl = 'https://api.gouv.fr/les-api/api-entreprise/';

	/** {@inheritdoc} */
	protected $fields = [
		'companyName' => [
			'labelModule' => '_Base',
			'label' => 'Account name',
			'typeofdata' => 'V~O',
		],
		'sicCode' => [
			'labelModule' => '_Base',
			'label' => 'SIC code',
			'typeofdata' => 'V~O',
		],
		'vatNumber' => [
			'labelModule' => '_Base',
			'label' => 'VAT',
			'typeofdata' => 'V~O',
		],
	];

	/** {@inheritdoc} */
	protected $modulesFieldsMap = [
		'Accounts' => [
			'companyName' => 'accountname',
			'sicCode' => 'siccode',
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

	/** @var string CH sever address */
	private $url = 'https://recherche-entreprises.api.gouv.fr/';

	/** @var int count of elements for page */
	const PER_PAGE = 5;

	/** @var int number of page */
	const PAGE = 1;

	/** {@inheritdoc} */
	public function search(): array
	{
		if (!$this->isActive()) {
			return [];
		}
		$vatNumber = str_replace([' ', ',', '.', '-'], '', $this->request->getByType('vatNumber', 'Text'));
		$sicCode = str_replace([' ', ',', '.', '-'], '', $this->request->getByType('sicCode', 'Text'));
		$companyName = str_replace([' ', ',', '.', '-'], '', $this->request->getByType('companyName', 'Text'));
		$query = [];

		if(empty($vatNumber) && empty($sicCode) && empty($companyName)) {
			return [];
		}

		if (!empty($vatNumber)) {
			$query['q'] = $vatNumber;
		} elseif (!empty($companyName) && empty($vatNumber)) {
			$query['q'] = $companyName;
		}
		$query['page'] = self::PAGE;
		$query['per_page'] = self::PER_PAGE;
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
	 * @param array  $query
	 *
	 * @return void
	 */
	private function getDataFromApi(array $query): void
	{
		$response = [];
		try {
			$response = \App\RequestHttp::getClient()->get($this->url . 'search?' . http_build_query($query), []);
		} catch (\GuzzleHttp\Exception\ClientException $e) {
			\App\Log::warning($e->getMessage(), 'RecordCollectors');
			$this->response['error'] = $e->getMessage();
		}

		$data = isset($response) ? \App\Json::decode($response->getBody()->getContents()) : [];
		if (empty($data)) {
			return;
		}

		foreach($data['results'] as $key => $result) {
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
