<?php
/**
 * YetiForce shop YetiForceUkCompaniesHouse file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\YetiForce\Shop\Product;

/**
 * YetiForce shop YetiForceUkCompaniesHouse class.
 */
class YetiForceUkCompaniesHouse extends \App\YetiForce\Shop\AbstractBaseProduct
{
	/** {@inheritdoc} */
	public $label = 'YetiForce Companies House UK';

	/** {@inheritdoc} */
	public $category = 'Integrations';

	/** {@inheritdoc} */
	public $website = 'https://yetiforce.com/en/yetiforce-orb';

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
		if (\App\YetiForce\Register::getProducts('YetiForceUkCompaniesHouse')) {
			[$status, $message] = \App\YetiForce\Shop::checkWithMessage('YetiForceUkCompaniesHouse');
		} else {
			if (
				(new \App\Db\Query())->from('vtiger_links')->where(['linktype' => 'EDIT_VIEW_RECORD_COLLECTOR', 'linklabel' => 'UKCompaniesHouse'])->exists()
				 || (new \App\Db\Query())->from('com_vtiger_workflowtasks')->where(['like', 'task', '%\UKCompaniesHouse";%', false])->exists()
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
				'linkicon' => 'fab fa-nfc-symbol mr-2',
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
		], $return);
	}
}
