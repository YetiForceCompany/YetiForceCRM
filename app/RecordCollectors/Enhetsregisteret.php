<?php
/**
 * Api Government of Norway file.
 *
 * @package App
 *
 * @see https://www.brreg.no/produkter-og-tjenester/apne-data/
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Rembiesa <s.rembiesa@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\RecordCollectors;

/**
 * Api Government of Norway class.
 */
class Enhetsregisteret extends Base
{
	/** {@inheritdoc} */
	protected static $allowedModules = ['Accounts', 'Leads', 'Vendors', 'Partners', 'Competition'];

	/** {@inheritdoc} */
	public $icon = 'fas fa-skiing-nordic';

	/** {@inheritdoc} */
	public $label = 'LBL_ENHETSREGISTERET';

	/** {@inheritdoc} */
	public $displayType = 'FillFields';

	/** {@inheritdoc} */
	public $description = 'LBL_ENHETSREGISTERET_DESC';

	/** {@inheritdoc} */
	public $docUrl = 'https://www.brreg.no/produkter-og-tjenester/apne-data/';

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
			'organisasjonsform_linksSelfHref' => 'website',
			'forretningsadresseAdresse0' => 'addresslevel8a',
			'forretningsadressePostnummer' => 'addresslevel7a',
			'forretningsadressePoststed' => 'addresslevel5a',
			'forretningsadresseKommune' => 'addresslevel4a',
			'forretningsadresseLand' => 'addresslevel1a',
		],
		'Leads' => [
			'navn' => 'company',
			'organisasjonsnummer' => 'registration_number_1',
			'organisasjonsform_linksSelfHref' => 'website',
			'forretningsadresseAdresse0' => 'addresslevel8a',
			'forretningsadressePostnummer' => 'addresslevel7a',
			'forretningsadressePoststed' => 'addresslevel5a',
			'forretningsadresseKommune' => 'addresslevel4a',
			'forretningsadresseLand' => 'addresslevel1a',
		],
		'Vendors' => [
			'navn' => 'vendorname',
			'organisasjonsnummer' => 'registration_number_1',
			'organisasjonsform_linksSelfHref' => 'website',
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

	/** @var string Enhetsregisteret sever address */
	private $url = 'https://data.brreg.no/enhetsregisteret/api/enheter/';

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
	 * @return void
	 */
	private function getDataFromApi(string $companyNumber): void
	{
		$response = [];
		try {
			$response = \App\RequestHttp::getClient()->get($this->url . $companyNumber);
		} catch (\GuzzleHttp\Exception\ClientException $e) {
			\App\Log::warning($e->getMessage(), 'RecordCollectors');
			$this->response['error'] = $e->getMessage();
			if ($e->getCode() === 400) {
				$this->response['error'] = \App\Language::translate('LBL_ENHETSREGISTERET_400', 'Other.RecordCollector');
				return;
			}
		}
		$this->data = isset($response) ? $this->parseData(\App\Json::decode($response->getBody()->getContents())) : [];
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
