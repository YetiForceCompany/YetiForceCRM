<?php
/**
 * VAT Payer Status Verification in Poland record collector file.
 * https://sprawdz-status-vat.mf.gov.pl/?wsdl.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Rembiesa <s.rembiesa@yetiforce.com>
 */

namespace App\RecordCollectors;

/**
 * VAT Payer Status Verification in Poland record collector class.
 */
class PlVatPayerStatusVerification extends Base
{
	/** {@inheritdoc} */
	protected static $allowedModules = ['Accounts', 'Leads', 'Vendors', 'Competition'];

	/** {@inheritdoc} */
	public $icon = 'fa-solid fa-magnifying-glass-dollar';

	/** {@inheritdoc} */
	public $label = 'LBL_PL_VAT_PAYER_LABEL';

	/** {@inheritdoc} */
	public $displayType = 'Summary';

	/** {@inheritdoc} */
	public $description = 'LBL_PL_VAT_PAYER_DESC';

	/** {@inheritdoc} */
	protected $fields = [
		'vatNumber' => [
			'labelModule' => '_Base',
			'label' => 'Vat ID',
			'typeofdata' => 'V~M',
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
	];

	/** @var string MF sever address */
	protected $url = 'https://sprawdz-status-vat.mf.gov.pl/?wsdl';

	/** {@inheritdoc} */
	public function search(): array
	{
		$vatNumber = str_replace([' ', ',', '.', '-'], '', $this->request->getByType('vatNumber', 'Text'));
		if (!$vatNumber) {
			return [];
		}
		if ($client = new \SoapClient($this->url, \App\RequestHttp::getSoapOptions())) {
			try {
				$r = $client->sprawdzNIP($vatNumber);
				$response['fields'] = [
					'' => $r->Komunikat
				];
			} catch (\SoapFault $e) {
				\App\Log::warning($e->faultstring, 'RecordCollectors');
				$response['error'] = $e->faultstring;
			}
		}
		return $response;
	}
}
