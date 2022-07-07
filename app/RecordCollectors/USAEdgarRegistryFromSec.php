<?php
/**
 * EDGAR Registry of Securities and Exchange Commission file.
 *
 * @package App
 *
 * @see https://www.sec.gov/edgar/sec-api-documentation
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Rembiesa <s.rembiesa@yetiforce.com>
 */

namespace App\RecordCollectors;

/**
 * EDGAR Registry of Securities and Exchange Commission class.
 */
class USAEdgarRegistryFromSec extends Base
{
	/** {@inheritdoc} */
	protected static $allowedModules = ['Accounts', 'Leads', 'Partners', 'Vendors', 'Competition'];

	/** {@inheritdoc} */
	public $icon = 'fa-solid fa-flag-usa';

	/** {@inheritdoc} */
	public $label = 'LBL_USA_EDGAR';

	/** {@inheritdoc} */
	public $displayType = 'FillFields';

	/** {@inheritdoc} */
	public $description = 'LBL_USA_EDGAR_DESC';

	/** {@inheritdoc} */
	protected $fields = [
		'cik' => [
			'labelModule' => '_Base',
			'label' => 'Registration number 1',
			'typeofdata' => 'V~O',
		]
	];

	/** {@inheritdoc} */
	protected $modulesFieldsMap = [
		'Accounts' => [
			'cik' => 'registration_number_1',
		],
	];

	/** {@inheritdoc} */
	public $formFieldsToRecordMap = [
		'Accounts' => [
			'name' => 'accountname',
			'cik' => 'registration_number_1',
			'ein' => 'vat_id',
			'webiste' => 'website',
			'sic' => 'siccode',
			'addressesBusinessStreet1' => 'addresslevel8a',
			'addressesBusinessStreet2' => 'buildingnumbera',
			'addressesBusinessCity' => 'addresslevel5a',
			'addressesBusinessZipCode' => 'addresslevel7a',
			'addressesBusinessStateOrCountryDescription' => 'addresslevel2a',
			'addressesMailingStreet1' => 'addresslevel8c',
			'addressesMailingStreet2' => 'buildingnumberc',
			'addressesMailingsCity' => 'addresslevel5c',
			'addressesMailingZipCode' => 'addresslevel7c',
			'addressesBusinessStateOrCountryDescription' => 'addresslevel2c',
			'phone' => 'phone'
		],
		'Leads' => [
			'name' => 'company',
			'cik' => 'registration_number_1',
			'ein' => 'vat_id',
			'webiste' => 'website',
			'sic' => 'siccode',
			'addressesBusinessStreet1' => 'addresslevel8a',
			'addressesBusinessStreet2' => 'buildingnumbera',
			'addressesBusinessCity' => 'addresslevel5a',
			'addressesBusinessZipCode' => 'addresslevel7a',
			'addressesBusinessStateOrCountryDescription' => 'addresslevel2a',
			'phone' => 'phone'
		],
		'Partners' => [
			'name' => 'subject',
			'ein' => 'vat_id',
			'addressesBusinessStreet1' => 'addresslevel8a',
			'addressesBusinessStreet2' => 'buildingnumbera',
			'addressesBusinessCity' => 'addresslevel5a',
			'addressesBusinessZipCode' => 'addresslevel7a',
			'addressesBusinessStateOrCountryDescription' => 'addresslevel2a'
		],
		'Vendors' => [
			'name' => 'vendorname',
			'cik' => 'registration_number_1',
			'ein' => 'vat_id',
			'webiste' => 'website',
			'addressesBusinessStreet1' => 'addresslevel8a',
			'addressesBusinessStreet2' => 'buildingnumbera',
			'addressesBusinessCity' => 'addresslevel5a',
			'addressesBusinessZipCode' => 'addresslevel7a',
			'addressesBusinessStateOrCountryDescription' => 'addresslevel2a',
			'addressesMailingStreet1' => 'addresslevel8c',
			'addressesMailingStreet2' => 'buildingnumberc',
			'addressesMailingsCity' => 'addresslevel5c',
			'addressesMailingZipCode' => 'addresslevel7c',
			'addressesBusinessStateOrCountryDescription' => 'addresslevel2c',
			'phone' => 'phone'
		],
		'Competition' => [
			'name' => 'subject',
			'ein' => 'vat_id',
			'addressesBusinessStreet1' => 'addresslevel8a',
			'addressesBusinessStreet2' => 'buildingnumbera',
			'addressesBusinessCity' => 'addresslevel5a',
			'addressesBusinessZipCode' => 'addresslevel7a',
			'addressesBusinessStateOrCountryDescription' => 'addresslevel2a'
		]
	];

	/** @var string SEC sever address */
	protected $url = 'https://data.sec.gov/submissions/CIK';

	/** @var string Url to Documentation API */
	public $docUrl = 'https://www.sec.gov/edgar/sec-api-documentation';

	/** @var int Central Index Key length */
	const CIK_LEN = 10;

	/** {@inheritdoc} */
	public function search(): array
	{
		$this->moduleName = $this->request->getModule();
		$cik = str_replace([' ', ',', '.', '-'], '', $this->request->getByType('cik', 'Text'));

		if (!$cik) {
			return [];
		}
		if (\strlen($cik) < self::CIK_LEN) {
			$countZeroToAdd = self::CIK_LEN - \strlen($cik);
			$cik = str_repeat('0', $countZeroToAdd) . $cik;
		}
		$this->data = $this->parseData($this->getDataFromApi($cik));
		$this->loadData();
		return $this->response;
	}

	/**
	 * Function finding company data by  Central Index Key (CIK).
	 *
	 * @param string $cik
	 *
	 * @return array
	 */
	private function getDataFromApi(string $cik): array
	{
		$response = [];
		try {
			$response = \App\RequestHttp::getClient()->get($this->url . $cik . '.json', [
				'headers' => [
					'User-Agent' => 'YetiForce S. A. devs@yetiforce.com',
				],
			]);
		} catch (\GuzzleHttp\Exception\ClientException $e) {
			\App\Log::warning($e->getMessage(), 'RecordCollectors');
			$this->response['error'] = $e->getMessage();
		}
		return isset($response) ? \App\Json::decode($response->getBody()->getContents()) : [];
	}

	/**
	 * Function parsing data to fields from Securities and Exchange Commission API.
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	private function parseData(array $data): array
	{
		if (empty($data)) {
			return [];
		}
		return \App\Utils::flattenKeys($data, 'ucfirst');
	}
}
