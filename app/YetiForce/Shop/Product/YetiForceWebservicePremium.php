<?php
/**
 * YetiForce shop YetiForce Webservice Premium file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\YetiForce\Shop\Product;

/**
 * YetiForce shop YetiForce Webservice Premium class.
 */
class YetiForceWebservicePremium extends \App\YetiForce\Shop\AbstractBaseProduct
{
	/** {@inheritdoc} */
	public $label = 'YetiForce Webservice Premium';

	/** {@inheritdoc} */
	public $category = 'Integrations';

	/** {@inheritdoc} */
	public $website = 'https://yetiforce.com/en/yetiforce-webservice-premium';

	/** {@inheritdoc} */
	public $prices = [
		'Micro' => 45,
		'Small' => 85,
		'Medium' => 165,
		'Large' => 325,
		'Corporation' => 645,
	];

	/** {@inheritdoc} */
	public $featured = true;

	/** {@inheritdoc} */
	public function verify(): array
	{
		$message = $status = true;
		if (\App\YetiForce\Register::getProducts('YetiForceWebservicePremium')) {
			[$status, $message] = \App\YetiForce\Shop::checkWithMessage('YetiForceWebservicePremium');
		} else {
			if ((new \App\Db\Query())->from('w_#__servers')->where(['type' => 'WebservicePremium'])->exists()) {
				$message = 'LBL_PAID_FUNCTIONALITY_ACTIVATED';
				$status = false;
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
		if (\App\Security\AdminAccess::isPermitted('WebserviceApps')) {
			$links[] = \Vtiger_Link_Model::getInstanceFromValues([
				'linklabel' => 'WebserviceApps',
				'relatedModuleName' => 'Settings:WebserviceApps',
				'linkicon' => 'adminIcon-webservice-apps mr-2',
				'linkhref' => true,
				'linkurl' => 'index.php?module=WebserviceApps&view=Index&parent=Settings',
				'linkclass' => 'btn-primary',
				'showLabel' => 1,
			]);
		}
		if (\App\Security\AdminAccess::isPermitted('WebserviceUsers')) {
			$links[] = \Vtiger_Link_Model::getInstanceFromValues([
				'linklabel' => 'WebserviceUsers',
				'relatedModuleName' => 'Settings:WebserviceUsers',
				'linkicon' => 'adminIcon-webservice-users mr-2',
				'linkhref' => true,
				'linkurl' => 'index.php?module=WebserviceUsers&view=List&parent=Settings&typeApi=WebservicePremium',
				'linkclass' => 'btn-primary',
				'showLabel' => 1,
			]);
		}
		return $links;
	}
}
