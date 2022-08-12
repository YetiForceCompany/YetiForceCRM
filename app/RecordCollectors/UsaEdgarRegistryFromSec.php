<?php
/**
 * EDGAR Registry of Securities and Exchange Commission file.
 *
 * @see https://www.sec.gov/edgar/sec-api-documentation
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
 * EDGAR Registry of Securities and Exchange Commission class.
 */
class UsaEdgarRegistryFromSec extends Base
{
	/** {@inheritdoc} */
	public $allowedModules = ['Accounts', 'Leads', 'Partners', 'Vendors', 'Competition'];

	/** {@inheritdoc} */
	public $icon = 'yfi-edgar-usa';

	/** {@inheritdoc} */
	public $label = 'LBL_USA_EDGAR';

	/** {@inheritdoc} */
	public $displayType = 'FillFields';

	/** {@inheritdoc} */
	public $description = 'LBL_USA_EDGAR_DESC';

	/** {@inheritdoc} */
	public $docUrl = 'https://www.sec.gov/edgar/sec-api-documentation';

	/** @var string SEC sever address */
	protected $url = 'https://data.sec.gov/submissions/CIK';

	/** {@inheritdoc} */
	protected $fields = [
		'cik' => [
			'labelModule' => '_Base',
			'label' => 'Registration number 1',
			'typeofdata' => 'V~M',
		]
	];

	/** {@inheritdoc} */
	protected $modulesFieldsMap = [
		'Accounts' => [
			'cik' => 'registration_number_1',
		],
		'Leads' => [
			'cik' => 'registration_number_1',
		],
		'Vendors' => [
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
			'phone' => 'phone',
			'addressesBusinessStreet2' => 'buildingnumbera',
			'addressesBusinessStreet1' => 'addresslevel8a',
			'addressesBusinessZipCode' => 'addresslevel7a',
			'addressesBusinessCity' => 'addresslevel5a',
			'addressesBusinessStateOrCountryDescription' => 'addresslevel2a',
			'addressesMailingStreet2' => 'buildingnumberb',
			'addressesMailingStreet1' => 'addresslevel8b',
			'addressesMailingZipCode' => 'addresslevel7b',
			'addressesMailingsCity' => 'addresslevel5b',
			'addressesBusinessStateOrCountryDescription' => 'addresslevel2b'
		],
		'Leads' => [
			'name' => 'company',
			'cik' => 'registration_number_1',
			'ein' => 'vat_id',
			'webiste' => 'website',
			'sic' => 'siccode',
			'phone' => 'phone',
			'addressesBusinessStreet2' => 'buildingnumbera',
			'addressesBusinessStreet1' => 'addresslevel8a',
			'addressesBusinessZipCode' => 'addresslevel7a',
			'addressesBusinessCity' => 'addresslevel5a',
			'addressesBusinessStateOrCountryDescription' => 'addresslevel2a',
		],
		'Partners' => [
			'name' => 'subject',
			'ein' => 'vat_id',
			'addressesBusinessStreet2' => 'buildingnumbera',
			'addressesBusinessStreet1' => 'addresslevel8a',
			'addressesBusinessZipCode' => 'addresslevel7a',
			'addressesBusinessCity' => 'addresslevel5a',
			'addressesBusinessStateOrCountryDescription' => 'addresslevel2a',
		],
		'Vendors' => [
			'name' => 'vendorname',
			'cik' => 'registration_number_1',
			'ein' => 'vat_id',
			'webiste' => 'website',
			'phone' => 'phone',
			'addressesBusinessStreet2' => 'buildingnumbera',
			'addressesBusinessStreet1' => 'addresslevel8a',
			'addressesBusinessZipCode' => 'addresslevel7a',
			'addressesBusinessCity' => 'addresslevel5a',
			'addressesBusinessStateOrCountryDescription' => 'addresslevel2a',
			'addressesMailingStreet2' => 'buildingnumberb',
			'addressesMailingStreet1' => 'addresslevel8b',
			'addressesMailingZipCode' => 'addresslevel7b',
			'addressesMailingsCity' => 'addresslevel5b',
			'addressesBusinessStateOrCountryDescription' => 'addresslevel2b'
		],
		'Competition' => [
			'name' => 'subject',
			'ein' => 'vat_id',
			'addressesBusinessStreet2' => 'buildingnumbera',
			'addressesBusinessStreet1' => 'addresslevel8a',
			'addressesBusinessZipCode' => 'addresslevel7a',
			'addressesBusinessCity' => 'addresslevel5a',
			'addressesBusinessStateOrCountryDescription' => 'addresslevel2a',
		]
	];

	/** @var int Central Index Key length */
	const CIK_LEN = 10;

	/** {@inheritdoc} */
	public function search(): array
	{
		if (!$this->isActive()) {
			return [];
		}
		$cik = str_replace([' ', ',', '.', '-'], '', $this->request->getByType('cik', 'Text'));
		if (!$cik) {
			return [];
		}
		$this->getDataFromApi($cik);
		$this->parseData();
		$this->loadData();
		return $this->response;
	}

	/**
	 * Function finding company data by Central Index Key (CIK).
	 *
	 * @param string $cik
	 *
	 * @return void
	 */
	private function getDataFromApi(string $cik): void
	{
		if (\strlen($cik) < self::CIK_LEN) {
			$countZeroToAdd = self::CIK_LEN - \strlen($cik);
			$cik = str_repeat('0', $countZeroToAdd) . $cik;
		}
		try {
			$response = \App\RequestHttp::getClient()->get($this->url . $cik . '.json', [
				'headers' => [
					'User-Agent' => 'YetiForce S. A. devs@yetiforce.com',
				],
			]);
			$this->data = isset($response) ? \App\Json::decode($response->getBody()->getContents()) : [];
		} catch (\GuzzleHttp\Exception\GuzzleException $e) {
			\App\Log::warning($e->getMessage(), 'RecordCollectors');
			$this->response['error'] = $e->getMessage();
		}
	}

	/**
	 * Function parsing data to fields from Securities and Exchange Commission API.
	 *
	 * @return void
	 */
	private function parseData(): void
	{
		if (empty($this->data)) {
			return;
		}
		unset($this->data['filings']);
		$this->data = \App\Utils::flattenKeys($this->data, 'ucfirst');
	}
}
