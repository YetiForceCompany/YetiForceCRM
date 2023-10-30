<?php
/**
 * YetiForce shop YetiForce RBL file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\YetiForce\Shop\Product;

/**
 * YetiForce shop YetiForce RBL class.
 */
class YetiForceRbl extends \App\YetiForce\Shop\AbstractBaseProduct
{
	/** {@inheritdoc} */
	protected bool $disabled = true;

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
		if (\App\Security\AdminAccess::isPermitted('MailRbl')) {
			$links[] = \Vtiger_Link_Model::getInstanceFromValues([
				'linklabel' => 'LBL_PUBLIC_RBL',
				'relatedModuleName' => 'Settings:MailRbl',
				'linkicon' => 'yfi yfi-rbl mr-2',
				'linkhref' => true,
				'linkurl' => 'index.php?parent=Settings&module=MailRbl&view=Index&tab=publicRbl',
				'linkclass' => 'btn-primary',
				'showLabel' => 1,
			]);
		}
		return $links;
	}
}
