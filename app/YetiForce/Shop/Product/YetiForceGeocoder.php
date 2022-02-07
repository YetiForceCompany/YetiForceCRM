<?php
/**
 * YetiForce shop YetiForceGeocoder file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\YetiForce\Shop\Product;

/**
 * YetiForce shop YetiForceGeocoder class.
 */
class YetiForceGeocoder extends \App\YetiForce\Shop\AbstractBaseProduct
{
	/** {@inheritdoc} */
	public $label = 'YetiForce Address Search';

	/** {@inheritdoc} */
	public $category = 'Addons';

	/** {@inheritdoc} */
	public $website = 'https://yetiforce.com/en/yetiforce-address-search-en';

	/** {@inheritdoc} */
	public $prices = [
		'Micro' => 5,
		'Small' => 12,
		'Medium' => 25,
		'Large' => 50,
		'Corporation' => 100,
	];

	/** {@inheritdoc} */
	public $featured = true;

	/** {@inheritdoc} */
	public function verify(): array
	{
		$message = $status = true;
		if (\App\YetiForce\Register::getProducts('YetiForceGeocoder')) {
			[$status, $message] = \App\YetiForce\Shop::checkWithMessage('YetiForceGeocoder');
		} else {
			$pwnedPassword = \App\Extension\PwnedPassword::getDefaultProvider();
			if ('\App\Extension\PwnedPassword\YetiForce' === \get_class($pwnedPassword)) {
				if ($pwnedPassword->isActive()) {
					$message = 'LBL_PAID_FUNCTIONALITY_ACTIVATED';
					$status = false;
				}
			}
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
		if (\App\Security\AdminAccess::isPermitted('ApiAddress')) {
			$links[] = \Vtiger_Link_Model::getInstanceFromValues([
				'linklabel' => 'LBL_API_ADDRESS',
				'relatedModuleName' => 'Settings:_Base',
				'linkicon' => 'adminIcon-address',
				'linkhref' => true,
				'linkurl' => 'index.php?module=ApiAddress&parent=Settings&view=Configuration',
				'linkclass' => 'btn-primary',
				'showLabel' => 1,
			]);
		}
		return $links;
	}
}
