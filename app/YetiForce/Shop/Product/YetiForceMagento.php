<?php
/**
 * YetiForce shop YetiForceMagento file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\YetiForce\Shop\Product;

/**
 * YetiForce shop YetiForceMagento class.
 */
class YetiForceMagento extends \App\YetiForce\Shop\AbstractBaseProduct
{
	/** {@inheritdoc} */
	protected bool $disabled = true;

	/** {@inheritdoc} */
	public function analyzeConfiguration(): array
	{
		return !\Settings_Magento_Module_Model::isActive() ? [
			'message' => \App\Language::translateArgs('LBL_FUNCTIONALITY_HAS_NOT_YET_BEEN_ACTIVATED', 'Settings:Magento', 'Magento'),
			'type' => 'LBL_REQUIRES_INTERVENTION',
			'href' => 'index.php?parent=Settings&module=Magento&view=List',
		] : [];
	}

	/** {@inheritdoc} */
	public function isConfigured(): bool
	{
		return \Settings_Magento_Module_Model::isActive();
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
		if (\App\Security\AdminAccess::isPermitted('Magento')) {
			$links[] = \Vtiger_Link_Model::getInstanceFromValues([
				'linklabel' => 'LBL_MAGENTO',
				'relatedModuleName' => 'Settings:Magento',
				'linkicon' => 'fab fa-magento',
				'linkhref' => true,
				'linkurl' => 'index.php?parent=Settings&module=Magento&view=List',
				'linkclass' => 'btn-primary',
				'showLabel' => 1,
			]);
		}
		return $links;
	}
}
