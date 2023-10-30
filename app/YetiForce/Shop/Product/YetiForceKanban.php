<?php
/**
 * YetiForce shop YetiForceKanban file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\YetiForce\Shop\Product;

/**
 * YetiForce shop YetiForceKanban class.
 */
class YetiForceKanban extends \App\YetiForce\Shop\AbstractBaseProduct
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
		if (\App\Security\AdminAccess::isPermitted('Kanban')) {
			$links[] = \Vtiger_Link_Model::getInstanceFromValues([
				'linklabel' => 'LBL_KANBAN',
				'relatedModuleName' => 'Settings:Kanban',
				'linkicon' => 'yfi yfi-kanban',
				'linkhref' => true,
				'linkurl' => 'index.php?parent=Settings&module=Kanban&view=Index',
				'linkclass' => 'btn-primary',
				'showLabel' => 1,
			]);
		}
		return $links;
	}
}
