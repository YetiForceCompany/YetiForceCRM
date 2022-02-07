<?php
/**
 * YetiForce shop PDF Premium file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\YetiForce\Shop\Product;

/**
 * YetiForce shop PDF Premium class.
 */
class YetiForcePdfPremium extends \App\YetiForce\Shop\AbstractBaseProduct
{
	/** {@inheritdoc} */
	public $label = 'YetiForce PDF Premium';

	/** {@inheritdoc} */
	public $category = 'Addons';

	/** {@inheritdoc} */
	public $website = 'https://yetiforce.com/en/yetiforce-pdf-premium-en';

	/** {@inheritdoc} */
	public $prices = [
		'Micro' => 10,
		'Small' => 25,
		'Medium' => 50,
		'Large' => 100,
		'Corporation' => 500,
	];

	/** {@inheritdoc} */
	public $featured = true;

	/** {@inheritdoc} */
	public function verify(): array
	{
		$message = $status = true;
		if (\App\YetiForce\Register::getProducts('YetiForcePdfPremium')) {
			[$status, $message] = \App\YetiForce\Shop::checkWithMessage('YetiForcePdfPremium');
		} else {
			$message = 'LBL_PAID_FUNCTIONALITY_ACTIVATED';
			$status = !(new \App\Db\Query())->from('a_#__pdf')->where(['generator' => 'Chromium'])->exists();
		}
		return ['status' => $status, 'message' => $message];
	}

	/** {@inheritdoc} */
	public function getAdditionalButtons(): array
	{
		$links = [
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
		];
		if (\App\Security\AdminAccess::isPermitted('Companies')) {
			$links[] = \Vtiger_Link_Model::getInstanceFromValues([
				'linklabel' => 'LBL_PDF',
				'relatedModuleName' => 'Settings:PDF',
				'linkicon' => 'adminIcon-modules-pdf-templates',
				'linkhref' => true,
				'linkurl' => 'index.php?parent=Settings&module=PDF&view=List',
				'linkclass' => 'btn-primary',
				'showLabel' => 1,
			]);
		}
		return $links;
	}
}
