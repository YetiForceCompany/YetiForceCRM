<?php
/**
 * YetiForce shop YetiForceDav file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\YetiForce\Shop\Product;

/**
 * YetiForce shop YetiForceDav class.
 */
class YetiForceDav extends \App\YetiForce\Shop\AbstractBaseProduct
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
		if (\App\Security\AdminAccess::isPermitted('Dav')) {
			$links[] = \Vtiger_Link_Model::getInstanceFromValues([
				'linklabel' => 'LBL_DAV_KEYS',
				'relatedModuleName' => 'Settings:Dav',
				'linkicon' => 'yfi yfi-dav',
				'linkhref' => true,
				'linkurl' => 'index.php?parent=Settings&module=Dav&view=Keys',
				'linkclass' => 'btn-primary',
				'showLabel' => 1,
			]);
		}
		return $links;
	}
}
