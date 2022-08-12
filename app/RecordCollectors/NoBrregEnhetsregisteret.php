<?php
/**
 * The Norway Brønnøysund Register Centre Enhetsregisteret API file.
 *
 * @see https://www.brreg.no/produkter-og-tjenester/apne-data/
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
 * The Norway Brønnøysund Register Centre Enhetsregisteret API class.
 */
class NoBrregEnhetsregisteret extends Base
{
	/** {@inheritdoc} */
	public $allowedModules = ['Accounts', 'Leads', 'Vendors', 'Partners', 'Competition'];

	/** {@inheritdoc} */
	public $icon = 'yfi-enhetsregisteret-no';

	/** {@inheritdoc} */
	public $label = 'LBL_NO_BRREG_ENHETSREGISTERET';

	/** {@inheritdoc} */
	public $displayType = 'FillFields';

	/** {@inheritdoc} */
	public $description = 'LBL_NO_BRREG_ENHETSREGISTERET_DESC';

	/** {@inheritdoc} */
	public $docUrl = 'https://www.brreg.no/produkter-og-tjenester/apne-data/';

	/** @var string CH sever address */
	const EXTERNAL_URL = 'https://data.brreg.no/enhetsregisteret/oppslag/enheter/';

	/** @var string Enhetsregisteret sever address */
	private $url = 'https://data.brreg.no/enhetsregisteret/api/enheter/';

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
			'navn' => 'accountname',
			'organisasjonsnummer' => 'registration_number_1',
			'naeringskode1Kode' => 'siccode',
			'forretningsadresseAdresse0' => 'addresslevel8a',
			'forretningsadressePostnummer' => 'addresslevel7a',
			'forretningsadressePoststed' => 'addresslevel5a',
			'forretningsadresseKommune' => 'addresslevel4a',
			'forretningsadresseLand' => 'addresslevel1a',
		],
		'Leads' => [
			'navn' => 'company',
			'organisasjonsnummer' => 'registration_number_1',
			'forretningsadresseAdresse0' => 'addresslevel8a',
			'forretningsadressePostnummer' => 'addresslevel7a',
			'forretningsadressePoststed' => 'addresslevel5a',
			'forretningsadresseKommune' => 'addresslevel4a',
			'forretningsadresseLand' => 'addresslevel1a',
		],
		'Vendors' => [
			'navn' => 'vendorname',
			'organisasjonsnummer' => 'registration_number_1',
			'forretningsadresseAdresse0' => 'addresslevel8a',
			'forretningsadressePostnummer' => 'addresslevel7a',
			'forretningsadressePoststed' => 'addresslevel5a',
			'forretningsadresseKommune' => 'addresslevel4a',
			'forretningsadresseLand' => 'addresslevel1a',
		],
		'Partners' => [
			'navn' => 'subject',
			'forretningsadresseAdresse0' => 'addresslevel8a',
			'forretningsadressePostnummer' => 'addresslevel7a',
			'forretningsadressePoststed' => 'addresslevel5a',
			'forretningsadresseKommune' => 'addresslevel4a',
			'forretningsadresseLand' => 'addresslevel1a',
		],
		'Competition' => [
			'navn' => 'subject',
			'forretningsadresseAdresse0' => 'addresslevel8a',
			'forretningsadressePostnummer' => 'addresslevel7a',
			'forretningsadressePoststed' => 'addresslevel5a',
			'forretningsadresseKommune' => 'addresslevel4a',
			'forretningsadresseLand' => 'addresslevel1a',
		],
	];

	/** {@inheritdoc} */
	public function search(): array
	{
		$companyNumber = str_replace([' ', ',', '.', '-'], '', $this->request->getByType('companyNumber', 'Text'));

		if (!$this->isActive() && empty($companyNumber)) {
			return [];
		}

		$this->getDataFromApi($companyNumber);
		$this->loadData();
		return $this->response;
	}

	/**
	 * Function fetching company data by Company Number (Organisasjonsnummer).
	 *
	 * @param string $companyNumber
	 *
	 * @return void
	 */
	private function getDataFromApi(string $companyNumber): void
	{
		$response = [];
		try {
			$response = \App\RequestHttp::getClient()->get($this->url . $companyNumber);
		} catch (\GuzzleHttp\Exception\GuzzleException $e) {
			\App\Log::warning($e->getMessage(), 'RecordCollectors');
			$this->response['error'] = $e->getMessage();
			if (400 === $e->getCode()) {
				$this->response['error'] = \App\Language::translate('LBL_NO_BRREG_ENHETSREGISTERET_400', 'Other.RecordCollector');
				return;
			}
		}
		$this->data = isset($response) ? $this->parseData(\App\Json::decode($response->getBody()->getContents())) : [];
		$this->response['links'][0] = self::EXTERNAL_URL . $companyNumber;
		unset($this->data['_linksSelfHref']);
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
