<?php
/**
 * Vatify API file.
 *
 * @see https://www.vatify.eu/coverage.html
 * @see https://api.vatify.eu/v1/demo/ TEST API URL
 * @see https://api.vatify.eu/v1/ PROD API URL
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
 * Vatify API class.
 */
class VatifyEu extends Base
{
	/** {@inheritdoc} */
	public $allowedModules = ['Accounts', 'Leads', 'Vendors', 'Partners', 'Competition'];

	/** {@inheritdoc} */
	public $icon = 'yfi-vatify-eu';

	/** {@inheritdoc} */
	public $label = 'LBL_VATIFY_EU';

	/** {@inheritdoc} */
	public $displayType = 'FillFields';

	/** {@inheritdoc} */
	public $description = 'LBL_VATIFY_DESC_EU';

	/** {@inheritdoc} */
	public $docUrl = 'https://www.vatify.eu/docs/api/getting-started/';

	/** {@inheritdoc} */
	private $url = 'https://api.vatify.eu/v1/';

	/** {@inheritdoc} */
	public $settingsFields = [
		'client_id' => ['required' => 1, 'purifyType' => 'Text', 'label' => 'LBL_CLIENT_ID'],
		'access_key' => ['required' => 1, 'purifyType' => 'Text', 'label' => 'LBL_ACCESS_KEY'],
	];
	/** @var string Access Key. */
	private $accessKey;

	/** @var string Client ID. */
	private $clientId;

	/** @var string Bearer Token. */
	private $bearerToken;

	/** {@inheritdoc} */
	protected $fields = [
		'country' => [
			'labelModule' => '_Base',
			'label' => 'Country',
			'picklistModule' => 'Other.Country',
			'typeofdata' => 'V~M',
			'uitype' => 16,
			'picklistValues' => [
				'AL' => 'Albania',
				'AT' => 'Austria',
				'BY' => 'Belarus',
				'BE' => 'Belgium',
				'BA' => 'Bosnia And Herzegovina',
				'BG' => 'Bulgaria',
				'CY' => 'Cyprus',
				'CZ' => 'Czech Republic',
				'DE' => 'Germany',
				'DK' => 'Denmark',
				'EE' => 'Estonia',
				'GB' => 'United Kingdom',
				'GR' => 'Greece',
				'ES' => 'Spain',
				'FI' => 'Finland',
				'FR' => 'France',
				'GB' => 'Northern Ireland',
				'GE' => 'Georgia',
				'HR' => 'Croatia',
				'HU' => 'Hungary',
				'IS' => 'Iceland',
				'IE' => 'Ireland',
				'IL' => 'Israel',
				'IT' => 'Italy',
				'KZ' => 'Kazakstan',
				'Kosovo' => 'Kosovo',
				'LV' => 'Latvia',
				'LI' => 'Liechtenstein',
				'LT' => 'Lithuania',
				'LU' => 'Luxembourg',
				'MK' => 'Macedonia, The Former Yugoslav Republic Of',
				'MT' => 'Malta',
				'MD' => 'Moldova, Republic Of',
				'ME' => 'Montenegro',
				'NO' => 'Norway',
				'NL' => 'Netherlands',
				'PL' => 'Poland',
				'PT' => 'Portugal',
				'RO' => 'Romania',
				'RU' => 'Russian Federation',
				'SE' => 'Sweden',
				'SK' => 'Slovenia',
				'RS' => 'Serbia',
				'CH' => 'Switzerland',
				'UA' => 'Ukraine',
				'ZA' => 'South Africa',
			]
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
	public $formFieldsToRecordMap = [
		'Accounts' => [
			'title' => 'accountname',
			'transliteratedTitle' => 'accountname',
			'registration_number' => 'registration_number_1',
			'identifier' => 'vat_id',
			'tax_id_number' => 'vat_id',
			'sector' => 'siccode',
			'transliteratedSector' => 'siccode',
			'address' => 'addresslevel8a',
			'transliteratedAddress' => 'addresslevel8a',
			'postal_code' => 'addresslevel7a',
			'city' => 'addresslevel5a',
			'transliteratedCity' => 'addresslevel5a',
			'community' => 'addresslevel4a',
			'region' => 'addresslevel2a',
			'transliteratedRegion' => 'addresslevel2a',
			'country' => 'addresslevel1a',
			'phone_number' => 'phone',
			'fax_number' => 'fax',
			'email_address' => 'email1',
		],
		'Leads' => [
			'title' => 'company',
			'transliteratedTitle' => 'company',
			'registration_number' => 'registration_number_1',
			'identifier' => 'vat_id',
			'tax_id_number' => 'vat_id',
			'address' => 'addresslevel8a',
			'transliteratedAddress' => 'addresslevel8a',
			'postal_code' => 'addresslevel7a',
			'city' => 'addresslevel5a',
			'transliteratedCity' => 'addresslevel5a',
			'community' => 'addresslevel4a',
			'region' => 'addresslevel2a',
			'transliteratedRegion' => 'addresslevel2a',
			'country' => 'addresslevel1a',
			'phone_number' => 'phone',
			'fax_number' => 'fax',
			'email_address' => 'email',
		],
		'Vendors' => [
			'title' => 'vendorname',
			'transliteratedTitle' => 'vendorname',
			'registration_number' => 'registration_number_1',
			'identifier' => 'vat_id',
			'tax_id_number' => 'vat_id',
			'transliteratedSector' => 'siccode',
			'address' => 'addresslevel8a',
			'transliteratedAddress' => 'addresslevel8a',
			'postal_code' => 'addresslevel7a',
			'city' => 'addresslevel5a',
			'transliteratedCity' => 'addresslevel5a',
			'community' => 'addresslevel4a',
			'region' => 'addresslevel2a',
			'transliteratedRegion' => 'addresslevel2a',
			'country' => 'addresslevel1a',
			'phone_number' => 'phone',
			'email_address' => 'email',
		],
		'Partners' => [
			'title' => 'subject',
			'transliteratedTitle' => 'subject',
			'identifier' => 'vat_id',
			'tax_id_number' => 'vat_id',
			'transliteratedSector' => 'siccode',
			'address' => 'addresslevel8a',
			'transliteratedAddress' => 'addresslevel8a',
			'postal_code' => 'addresslevel7a',
			'city' => 'addresslevel5a',
			'transliteratedCity' => 'addresslevel5a',
			'community' => 'addresslevel4a',
			'region' => 'addresslevel2a',
			'transliteratedRegion' => 'addresslevel2a',
			'country' => 'addresslevel1a',
			'email_address' => 'email',
		],
		'Competition' => [
			'title' => 'subject',
			'transliteratedTitle' => 'subject',
			'identifier' => 'vat_id',
			'tax_id_number' => 'vat_id',
			'address' => 'addresslevel8a',
			'transliteratedAddress' => 'addresslevel8a',
			'postal_code' => 'addresslevel7a',
			'city' => 'addresslevel5a',
			'transliteratedCity' => 'addresslevel5a',
			'community' => 'addresslevel4a',
			'region' => 'addresslevel2a',
			'transliteratedRegion' => 'addresslevel2a',
			'country' => 'addresslevel1a',
			'email_address' => 'email',
		],
	];

	/** {@inheritdoc} */
	public function isActive(): bool
	{
		return parent::isActive() && ($params = $this->getParams()) && !empty($params['access_key'] && !empty($params['client_id']));
	}

	/** {@inheritdoc} */
	public function search(): array
	{
		$country = $this->request->getByType('country', 'Text');
		$vatNumber = str_replace([' ', ',', '.', '-'], '', $this->request->getByType('vatNumber', 'Text'));

		if (!$this->isActive() || empty($country) || empty($vatNumber)) {
			return [];
		}
		$this->loadCredentials();
		$this->getBearerToken();
		$this->getDataFromApi([
			'country' => $country,
			'identifier' => $vatNumber,
		]);
		$this->parseData();
		$this->loadData();
		return $this->response;
	}

	/**
	 * Function fetching company data by VAT ID and Country.
	 *
	 * @param array $params
	 *
	 * @return void
	 */
	private function getDataFromApi(array $params): void
	{
		try {
			$client = \App\RequestHttp::getClient(['headers' => ['Authorization' => 'Bearer ' . $this->bearerToken]]);
			$response = $client->post($this->url . 'query', ['json' => $params]);
			$link = $response->getHeaderLine('location');
		} catch (\GuzzleHttp\Exception\GuzzleException $e) {
			\App\Log::warning($e->getMessage(), 'RecordCollectors');
			$this->response['error'] = $e->getResponse()->getReasonPhrase();
			return;
		}
		if (empty($link)) {
			return;
		}
		$response = null;
		$delay = $counter = 0;
		while (!$response && $counter < 5) {
			if ($delay < 10000000) {
				$delay += 500000;
			}
			usleep($delay);
			try {
				$response = $client->get($link);
				$body = \App\Json::decode($response->getBody()->getContents());
				if (202 === $response->getStatusCode() || 'FINISHED' !== $body['query']['status']) {
					$response = null;
				} else {
					$this->data = $body['result']['items'];
				}
			} catch (\GuzzleHttp\Exception\GuzzleException $e) {
				\App\Log::warning($e->getMessage(), 'RecordCollectors');
				$this->response['error'] = $e->getResponse()->getReasonPhrase();
				$response = true;
			}
			++$counter;
		}
	}

	/**
	 * Function parsing data to fields from API.
	 *
	 * @return void
	 */
	private function parseData(): void
	{
		if (empty($this->data)) {
			return;
		}
		foreach ($this->data as $key => $data) {
			$this->data[$key] = \App\Utils::flattenKeys($data['data'], 'ucfirst');
		}
	}

	/**
	 * Function setup Api Key.
	 *
	 * @return void
	 */
	private function loadCredentials(): void
	{
		if (($params = $this->getParams()) && !empty($params['client_id'] && !empty($params['access_key']))) {
			$this->clientId = $params['client_id'];
			$this->accessKey = $params['access_key'];
		}
	}

	/**
	 * Function fetching Bearer Token.
	 *
	 * @return void
	 */
	private function getBearerToken(): void
	{
		try {
			$response = \App\RequestHttp::getClient()->post($this->url . 'oauth2/token', [
				'headers' => [
					'Authorization' => 'Basic ' . base64_encode($this->clientId . ':' . $this->accessKey)
				],
				\GuzzleHttp\RequestOptions::JSON => [
					'grant_type' => 'client_credentials'
				]
			]);
			$response = \App\Json::decode($response->getBody()->getContents());
			$this->bearerToken = $response['access_token'];
		} catch (\GuzzleHttp\Exception\GuzzleException $e) {
			\App\Log::warning($e->getMessage(), 'RecordCollectors');
			$this->response['error'] = $e->getResponse()->getReasonPhrase();
			return;
		}
		if (empty($this->bearerToken)) {
			$this->response['error'] = \App\Language::translate('LBL_VATIFY_EU_NO_AUTH', 'Other.RecordCollector');
		}
	}
}
