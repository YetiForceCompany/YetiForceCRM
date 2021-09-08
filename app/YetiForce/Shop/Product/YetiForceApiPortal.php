<?php
/**
 * YetiForce shop YetiForce Api portal file.
 *
 * @package App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\YetiForce\Shop\Product;

/**
 * YetiForce shop YetiForce Api portal class.
 */
class YetiForceApiPortal extends \App\YetiForce\Shop\AbstractBaseProduct
{
	/** {@inheritdoc} */
	public $label = 'YetiForce API Portal';

	/** {@inheritdoc} */
	public $category = 'Integrations';

	/** {@inheritdoc} */
	public $website = 'https://yetiforce.com/en/yetiforce-portal';

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
		if (\App\YetiForce\Register::getProducts('YetiForceApiPortal')) {
			[$status, $message] = \App\YetiForce\Shop::checkWithMessage('YetiForceApiPortal');
		} else {
			if ((new \App\Db\Query())->from('w_#__servers')->where(['type' => 'Portal'])->exists()) {
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
				'linkurl' => 'index.php?module=WebserviceUsers&view=List&parent=Settings&typeApi=Portal',
				'linkclass' => 'btn-primary',
				'showLabel' => 1,
			]);
		}
		return $links;
	}
}
