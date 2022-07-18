<?php
/**
 * OpenCorporates API file.
 *
 * @package App
 *
 * @see https://api.opencorporates.com
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Rembiesa <s.rembiesa@yetiforce.com>
 */

namespace App\RecordCollectors;

use App\Exceptions\ApiException;
use Exception;
use JetBrains\PhpStorm\Internal\ReturnTypeContract;

/**
 * OpenCorporates API class.
 */
class OpenCorporates extends Base
{
	/** {@inheritdoc} */
	protected static $allowedModules = ['Accounts', 'Leads', 'Vendors', 'Partners', 'Competition'];

	/** {@inheritdoc} */
	public $icon = 'fas fa-book-open';

	/** {@inheritdoc} */
	public $label = 'LBL_OPEN_CORPORATES';

	/** {@inheritdoc} */
	public $displayType = 'FillFields';

	/** {@inheritdoc} */
	public $description = 'LBL_OPEN_CORPORATES_DESC';

	/** {@inheritdoc} */
	public $docUrl = 'https://api.opencorporates.com';

	/** {@inheritdoc} */
	protected $fields = [
		'countryCode' => [
			'label' => 'LBL_OPEN_CORPORATES_COUNTRY',
			'labelModule' => 'Other.RecordCollector',
			'picklistModule' => 'Other.Country',
			'uitype' => 16,
			'typeofdata' => 'V~0',
			'picklistValuesFunction' => 'getCodesFromApi',
		],
		'companyName' => [
			'labelModule' => '_Base',
			'label' => 'Account name',
		],
		'companyNumber' => [
			'labelModule' => '_Base',
			'label' => 'Registration number 1',
		],
		'taxNumber' => [
			'labelModule' => '_Base',
			'label' => 'Registration number 2',
		],
	];

	/** {@inheritdoc} */
	protected $modulesFieldsMap = [
		'Accounts' => [
			'companyName' => 'accountname',
			'companyNumber' => 'registration_number_1',
			'taxNumber' => 'registration_number_2'
		],
		'Leads' => [
			'companyName' => 'company',
			'companyNumber' => 'registration_number_1',
			'taxNumber' => 'registration_number_2'
		],
		'Vendors' => [
			'companyName' => 'vendorname',
			'companyNumber' => 'registration_number_1',
			'taxNumber' => 'registration_number_2'
		],
		'Partners' => [
			'companyName' => 'subject',
		],
		'Competition' => [
			'companyName' => 'subject',
		],
	];

	/** {@inheritdoc} */
	public $formFieldsToRecordMap = [
		'Accounts' => [
			'resultsCompanyName' => 'accountname',
			'resultsCompanyCompany_number' => 'registration_number_1',
			'resultsCompanyRegistry_url' => 'description'
		],
		'Leads' => [
			'resultsCompanyName' => 'company',
			'resultsCompanyCompany_number' => 'registration_number_1',
			'resultsCompanyRegistry_url' => 'description'
		],
		'Vendors' => [
			'resultsCompanyName' => 'vendorname',
			'resultsCompanyCompany_number' => 'registration_number_1',
			'resultsCompanyRegistry_url' => 'description'
		],
		'Partners' => [
			'resultsCompanyName' => 'subject',
			'resultsCompanyRegistry_url' => 'description'
		],
		'Competition' => [
			'resultsCompanyName' => 'subject',
			'resultsCompanyRegistry_url' => 'description'
		],
	];

	/** @var string OpenCorporates sever address */
	private $url = 'https://api.opencorporates.com/';

	/** @var string API version. */
	const API_VERSION = 'v0.4';

	/** {@inheritdoc} */
	public function search(): array
	{
		$countryCode = $this->request->getByType('countryCode', 'Text');
		$companyName = $this->request->getByType('companyName', 'Text');
		$companyNumber = str_replace([' ', ',', '.', '-'], '', $this->request->getByType('companyNumber', 'Text'));
		$taxNumber = str_replace([' ', ',', '.', '-'], '', $this->request->getByType('taxNumber', 'Text'));
		if (!$this->isActive() && empty($countryCode) && (empty($companyNumber) || empty($taxNumber || empty($companyName)))) {
			return [];
		}

		if (!empty($companyName)) {
			$this->getDataFromApiByName($companyName, $countryCode ?? '');
			$this->loadData();
			return $this->response;
		}

		$params = [];
		if (!empty($companyNumber)) {
			$params['companyNumber'] = $companyNumber;
		}
		if (!empty($taxNumber)) {
			$params['taxNumber'] = $taxNumber;
		}
		$this->getDataFromApi($countryCode, $params);
		$this->loadData();
		return $this->response;
	}

	/**
	 * Function fetching company data by params.
	 *
	 * @param string   $countryCode
	 * @param string[] $params
	 *
	 * @return void
	 */
	private function getDataFromApi(string $countryCode, array $params): void
	{
		$response = [];
		if (isset($params['companyNumber'])) {
			$response = $this->apiConnection($countryCode, $params['companyNumber']);
		}
		if (isset($params['taxNumber']) && !isset($response['errorCode'])) {
			$response = $this->apiConnection($countryCode, $params['taxNumber']);
		}

		$this->data = $response;
	}

	/**
	 * Function fetching and setting codes (countries, districts) for&from OpenCorporates.
	 *
	 * @param array $data
	 *
	 * @return void
	 */
	public function getCodesFromApi(array $data): array
	{
		$response = [];
		try {
			$response = \App\Json::decode(\App\RequestHttp::getClient()->get($this->url . self::API_VERSION . '/jurisdictions')->getBody()->getContents());
		} catch (\GuzzleHttp\Exception\ClientException $e) {
			\App\Log::warning($e->getMessage(), 'RecordCollectors');
			if ($e->getCode() > 400) {
					$this->response['error'] = $e->getResponse()->getReasonPhrase();
				} else {
					$this->response['error'] = $e->getMessage();
			}
		}
		if (!isset($response['results'])) {
			throw new ApiException(\App\Language::translate('LBL_OPEN_CORPORATES_403', 'Other.RecordCollector'));
		}
		$codes = [];
		foreach ($response['results']['jurisdictions'] as $index => $jurisdiction) {
			$codes[$jurisdiction['jurisdiction']['code']] = $jurisdiction['jurisdiction']['country'] !== $jurisdiction['jurisdiction']['name'] ? \App\Language::translate($jurisdiction['jurisdiction']['country'], 'Country') . " ({$jurisdiction['jurisdiction']['name']})" : \App\Language::translate($jurisdiction['jurisdiction']['country'], 'Country');
		}

		return $codes;
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
	 * Function connect with API via GuzzleHttp.
	 *
	 * @param string $countryCode
	 * @param string $param
	 *
	 * @return array
	 */
	private function apiConnection(string $countryCode, string $param): array
	{
		$response = [];
		try {
			$response = $this->parseData(\App\Json::decode(\App\RequestHttp::getClient()->get($this->url . 'companies/' . $countryCode . '/' . $param)->getBody()->getContents()));
		} catch (\GuzzleHttp\Exception\ClientException $e) {
			switch ($e->getCode()) {
				case 403:
					$response['errorCode'] = $e->getCode();
					$response['error'] = \App\Language::translate('LBL_OPEN_CORPORATES_403', 'Other.RecordCollector');
					break;
				case 404:
					$response['errorCode'] = $e->getCode();
					$response['error'] = \App\Language::translate('LBL_OPEN_ORPORATES_404', 'Other.RecordCollector');
					break;
				default:
					$response['errorCode'] = $e->getCode();
					$response['error'] = $e->getResponse()->getReasonPhrase();
			}
		}
		return $response;
	}
	/**
	 * Function fetching data about companies form OpenCorporates API by name.
	 *
	 * @param string $companyName
	 * @return void
	 */
	private function getDataFromApiByName(string $companyName, string $countryCode = ''): void
	{
		$options = !empty($countryCode) ? $options['jurisdiction_code'] = $countryCode : [];
		try {
			$response = \App\Json::decode(\App\RequestHttp::getClient()->get($this->url . self::API_VERSION . '/companies/search?q=' . $companyName)->getBody()->getContents());
		} catch (\GuzzleHttp\Exception\ClientException $e) {
			$response['errorCode'] = $e->getCode();
			$response['error'] = 403 === $e->getCode() ? \App\Language::translate('LBL_OPEN_CORPORATES_403', 'Other.RecordCollector') :  $e->getResponse()->getReasonPhrase();
		}
		if (!$response) {
			return;
		}
		foreach ($response['results']['companies'] as $company) {
			$this->data[$company['company']['name']] = $this->apiConnection($company['company']['jurisdiction_code'], $company['company']['company_number']);
		}
	}
}
