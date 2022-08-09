<?php
/**
 * VAT Payer Status Verification in United Kingdom record collector file.
 *
 * @see https://developer.service.hmrc.gov.uk/api-documentation
 * @see https://developer.service.hmrc.gov.uk/api-documentation/docs/api
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
 * VAT Payer Status Verification in United Kingdom record collector class.
 */
class UkVatPayerStatusVerification extends Base
{
	/** {@inheritdoc} */
	public $allowedModules = ['Accounts', 'Leads', 'Vendors', 'Competition', 'Partners'];

	/** {@inheritdoc} */
	public $icon = 'yfi-vat-uk';

	/** {@inheritdoc} */
	public $label = 'LBL_UK_VAT_PAYER';

	/** {@inheritdoc} */
	public $displayType = 'Summary';

	/** {@inheritdoc} */
	public $description = 'LBL_UK_VAT_PAYER_DESC';

	/** {@inheritdoc} */
	public $docUrl = 'https://developer.service.hmrc.gov.uk/api-documentation';

	/** @var string API sever address */
	protected $url = 'https://api.service.hmrc.gov.uk/';

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
		'Partners' => [
			'vatNumber' => 'vat_id',
		],
	];

	/** {@inheritdoc} */
	public function search(): array
	{
		if (!$this->isActive()) {
			return [];
		}
		$vatNumber = str_replace([' ', ',', '.', '-'], '', $this->request->getByType('vatNumber', 'Text'));
		if (!$vatNumber) {
			return [];
		}
		$response = [];
		try {
			$response = \App\Json::decode(\App\RequestHttp::getClient()->get($this->url . 'organisations/vat/check-vat-number/lookup/' . $vatNumber)
				->getBody()
				->getContents());
		} catch (\GuzzleHttp\Exception\GuzzleException $e) {
			\App\Log::warning($e->getMessage(), 'RecordCollectors');
			$this->response['error'] = $e->getMessage();
		}
		if (isset($response['target'])) {
			$response['fields'] = [
				'' => \App\Language::translate('LBL_UK_VAT_PAYER_CONFIRM', 'Other.RecordCollector'),
				'Name' => $response['target']['name'],
				'Vat ID' => $response['target']['vatNumber'],
				'Address' => $response['target']['address']['line1'] . $response['target']['address']['line2'] . $response['target']['address']['line3'] . $response['target']['address']['line4'],
				'Postcode' => $response['target']['address']['postcode'],
				'Country' => $response['target']['address']['countryCode']
			];
		} else {
			$response['fields'] = [
				'' => \App\Language::translate('LBL_UK_VAT_PAYER_NOT_CONFIRM', 'Other.RecordCollector')
			];
		}
		return $response;
	}
}
