<?php
/**
 * YetiForce shop DisableBranding file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\YetiForce\Shop\Product;

/**
 * YetiForce shop DisableBranding class.
 */
class YetiForceDisableBranding extends \App\YetiForce\Shop\AbstractBaseProduct
{
	/** {@inheritdoc} */
	public $label = 'YetiForce Branding';

	/** {@inheritdoc} */
	public $category = 'Addons';

	/** {@inheritdoc} */
	public $website = 'https://yetiforce.com/en/yetiforce-branding-en';

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
		if (\App\YetiForce\Register::getProducts('YetiForceDisableBranding')) {
			[$status, $message] = \App\YetiForce\Shop::checkWithMessage('YetiForceDisableBranding');
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
				'linklabel' => 'LBL_COMPANY_DETAILS',
				'relatedModuleName' => 'Settings:_Base',
				'linkicon' => 'adminIcon-company-detlis',
				'linkhref' => true,
				'linkurl' => 'index.php?parent=Settings&module=Companies&view=List',
				'linkclass' => 'btn-primary',
				'showLabel' => 1,
			]);
		}
		return $links;
	}
}
