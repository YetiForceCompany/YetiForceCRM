<?php
/**
 * YetiForce shop YetiForce Widgets Premium file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\YetiForce\Shop\Product;

/**
 * YetiForce shop YetiForce Widgets Premium class.
 */
class YetiForceWidgets extends \App\YetiForce\Shop\AbstractBaseProduct
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
		if (\App\Security\AdminAccess::isPermitted('WidgetsManagement')) {
			$links[] = \Vtiger_Link_Model::getInstanceFromValues([
				'linklabel' => 'WidgetsManagement',
				'relatedModuleName' => 'Settings:WidgetsManagement',
				'linkicon' => 'adminIcon-widgets-configuration mr-2',
				'linkhref' => true,
				'linkurl' => 'index.php?module=WidgetsManagement&parent=Settings&view=Configuration',
				'linkclass' => 'btn-primary',
				'showLabel' => 1,
			]);
		}
		if (\App\Security\AdminAccess::isPermitted('Widgets')) {
			$links[] = \Vtiger_Link_Model::getInstanceFromValues([
				'linklabel' => 'Widgets',
				'relatedModuleName' => 'Settings:Widgets',
				'linkicon' => 'adminIcon-modules-widgets mr-2',
				'linkhref' => true,
				'linkurl' => 'index.php?module=Widgets&parent=Settings&view=Index',
				'linkclass' => 'btn-primary',
				'showLabel' => 1,
			]);
		}
		return $links;
	}
}
