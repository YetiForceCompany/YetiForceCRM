<?php
/**
 * Check Bank Accounts Numbers record collector file.
 *
 * @package App
 *
 * @see https://www.gov.pl/web/kas/api-wykazu-podatnikow-vat/
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Rembiesa <s.rembiesa@yetiforce.com>
 */

namespace App\RecordCollectors;

/**
 * Check Bank Accounts Numbers record collector class.
 */
class CheckBankAccount extends Base
{
	/** {@inheritdoc} */
	protected static $allowedModules = ['Accounts', 'Leads', 'Vendors', 'Competition', 'Partners'];

	/** {@inheritdoc} */
	public $icon = 'fa-solid fa-building-columns';

	/** {@inheritdoc} */
	public $label = 'LBL_POLAND_CHECK_BANK_ACCOUNT';

	/** {@inheritdoc} */
	public $displayType = 'Summary';

	/** {@inheritdoc} */
	public $description = 'LBL_POLAND_CHECK_BANK_ACCOUNT';

	/** {@inheritdoc} */
	protected $fields = [
		'vatNumber' => [
			'labelModule' => '_Base',
			'label' => 'Vat ID',
			'typeofdata' => 'V~0',
		],
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
		],
		'Partners' => [
			'vatNumber' => 'vat_id',
		],
	];

	/** @var string MF sever address */
	protected $url = 'https://wl-test.mf.gov.pl/';

	/** @var string Url to Documentation API */
	public $docUrl = 'https://www.gov.pl/web/kas/api-wykazu-podatnikow-vat';

	/** {@inheritdoc} */
	public function search(): array
	{
		$response = [];
		$vatNumber = str_replace([' ', ',', '.', '-'], '', $this->request->getByType('vatNumber', 'Text'));
		try {
			$response = (\App\RequestHttp::getClient(\App\RequestHttp::getOptions()))->request('GET', $this->url . 'api/search/nip/' . $vatNumber, [
				'verify' => false,
				'query' => [
					'date' => date('Y-m-d'),
				]
			]);
		} catch (\GuzzleHttp\Exception\ClientException $e) {
			\App\Log::warning($e->getMessage(), 'RecordCollectors');
			$response['error'] = $e->getMessage();
		}
		return [];
	}
}
