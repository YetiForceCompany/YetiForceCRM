<?php
/**
 * YetiForce shop YetiForce Vulnerabilities file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\YetiForce\Shop\Product;

/**
 * YetiForce shop YetiForce Vulnerabilities class.
 */
class YetiForceVulnerabilities extends \App\YetiForce\Shop\AbstractBaseProduct
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
		if (\App\Security\AdminAccess::isPermitted('Dependencies')) {
			$links[] = \Vtiger_Link_Model::getInstanceFromValues([
				'linklabel' => 'LBL_VULNERABILITIES',
				'relatedModuleName' => 'Settings:Dependencies',
				'linkicon' => 'yfi yfi-security-errors-2 mr-2',
				'linkhref' => true,
				'linkurl' => 'index.php?parent=Settings&module=Dependencies&view=Vulnerabilities',
				'linkclass' => 'btn-primary',
				'showLabel' => 1,
			]);
		}
		return $links;
	}
}
