<?php
/**
 * YetiForce shop YetiForceRcUkVatPayerStatus file.
 *
 * @see App\RecordCollectors\UkVatPayerStatusVerification
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\YetiForce\Shop\Product;

/**
 * YetiForce shop YetiForceRcUkVatPayerStatus class.
 */
class YetiForceRcUkVatPayerStatus extends \App\YetiForce\Shop\AbstractBaseProduct
{
	/** {@inheritdoc} */
	public $label = 'YetiForce VAT UK';

	/** {@inheritdoc} */
	public $category = 'RecordCollectors';

	/** {@inheritdoc} */
	public $website = 'https://yetiforce.com/en/yetiforce-vat-uk';

	/** {@inheritdoc} */
	public $prices = [
		'Micro' => 1,
		'Small' => 2,
		'Medium' => 4,
		'Large' => 8,
		'Corporation' => 15,
	];

	/** {@inheritdoc} */
	public $featured = true;

	/** {@inheritdoc} */
	public function verify(): array
	{
		$message = $status = true;
		if (\App\YetiForce\Register::getProducts('YetiForceRcUkVatPayerStatus')) {
			[$status, $message] = \App\YetiForce\Shop::checkWithMessage('YetiForceRcUkVatPayerStatus');
		} else {
			if (
				(new \App\Db\Query())->from('vtiger_links')->where(['linktype' => 'EDIT_VIEW_RECORD_COLLECTOR', 'linklabel' => 'UkVatPayerStatusVerification'])->exists()
			) {
				$message = 'LBL_PAID_FUNCTIONALITY_ACTIVATED';
				$status = false;
			}
		}
		return ['status' => $status, 'message' => $message];
	}

	/** {@inheritdoc} */
	public function getAdditionalButtons(): array
	{
		$return = [];
		if (\App\Security\AdminAccess::isPermitted('RecordCollector')) {
			$return[] = \Vtiger_Link_Model::getInstanceFromValues([
				'linklabel' => 'RecordCollector',
				'relatedModuleName' => 'Settings:RecordCollector',
				'linkicon' => 'yfi-record-collectors mr-2',
				'linkhref' => true,
				'linkurl' => 'index.php?parent=Settings&module=RecordCollector&view=List',
				'linkclass' => 'btn-primary',
				'showLabel' => 1,
			]);
		}
		return array_merge([
			\Vtiger_Link_Model::getInstanceFromValues([
				'linklabel' => 'Website',
				'relatedModuleName' => '_Base',
				'linkicon' => 'fas fa-globe mr-2',
				'linkhref' => true,
				'linkExternal' => true,
				'linktarget' => '_blank',
				'linkurl' => $this->website,
				'linkclass' => 'btn-info',
				'showLabel' => 1,
			]),
			\Vtiger_Link_Model::getInstanceFromValues([
				'linklabel' => 'gov.uk',
				'relatedModuleName' => 'Settings:_Base',
				'linkicon' => 'fa-solid fa-link',
				'linkhref' => true,
				'linkExternal' => true,
				'linktarget' => '_blank',
				'linkurl' => 'https://developer.service.hmrc.gov.uk/api-documentation',
				'linkclass' => 'btn-secondary',
				'showLabel' => 1,
			]),
		], $return);
	}
}
