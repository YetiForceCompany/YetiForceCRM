<?php
/**
 * VAT Payer Status Verification in United Kingdom record collector file.
 *
 * @package App
 *
 * @see https://developer.service.hmrc.gov.uk/api-documentation
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Rembiesa <s.rembiesa@yetiforce.com>
 */

namespace App\RecordCollectors;

/**
 * VAT Payer Status Verification in United Kingdom record collector class.
 */
class UKVatPayerStatusVerification extends Base
{
	/** {@inheritdoc} */
	protected static $allowedModules = ['Accounts', 'Leads', 'Vendors', 'Competition', 'Partners'];

	/** {@inheritdoc} */
	public $icon = 'fa-solid fa-archway';

	/** {@inheritdoc} */
	public $label = 'LBL_UK_VAT_PAYER';

	/** {@inheritdoc} */
	public $displayType = 'Summary';

	/** {@inheritdoc} */
	public $description = 'LBL_UK_VAT_PAYER_DESC';

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

	/** @var string HMRC sever address */
	protected $url = 'https://api.service.hmrc.gov.uk/';

	/** @var string Url to Documentation API */
	public $docUrl = 'https://developer.service.hmrc.gov.uk/api-documentation';

	/** {@inheritdoc} */
	public function search(): array
	{
		$response = [];
		$vatNumber = str_replace([' ', ',', '.', '-'], '', $this->request->getByType('vatNumber', 'Text'));
		if (!$vatNumber) {
			return [];
		}
		try {
			$response = \App\Json::decode(\App\RequestHttp::getClient()->get($this->url . 'organisations/vat/check-vat-number/lookup/' . $vatNumber)
				->getBody()
				->getContents());
		} catch (\GuzzleHttp\Exception\ClientException $e) {
			\App\Log::warning($e->getMessage(), 'RecordCollectors');
			$this->response['error'] = $e->getMessage();
		}
		if (isset($response['target'])) {
			$response['fields'] = [
				'' => \App\Language::translate('LBL_UK_VAT_PAYER_CONFIRM', 'Other.RecordCollector')
			];
		} else {
			$response['fields'] = [
				'' => \App\Language::translate('LBL_UK_VAT_PAYER_NOT_CONFIRM', 'Other.RecordCollector')
			];
		}

		return $response;
	}
}
