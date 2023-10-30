<?php
/**
 * YetiForce shop YetiForce Webservice Premium file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\YetiForce\Shop\Product;

/**
 * YetiForce shop YetiForce Webservice Premium class.
 */
class YetiForceWebservicePremium extends \App\YetiForce\Shop\AbstractBaseProduct
{
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
