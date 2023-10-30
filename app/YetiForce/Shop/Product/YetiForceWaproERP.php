<?php
/**
 * YetiForce shop YetiForceWaproERP file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\YetiForce\Shop\Product;

/**
 * YetiForce shop YetiForceWaproERP class.
 */
class YetiForceWaproERP extends \App\YetiForce\Shop\AbstractBaseProduct
{
	/** {@inheritdoc} */
	protected bool $disabled = true;

	/** {@inheritdoc} */
	public function analyzeConfiguration(): array
	{
		if (empty($this->expirationDate) || \Settings_Wapro_Activation_Model::check()) {
			return [];
		}
		return [
			'message' => \App\Language::translateArgs('LBL_FUNCTIONALITY_HAS_NOT_YET_BEEN_ACTIVATED', 'Settings:Wapro', 'Wapro ERP'),
			'type' => 'LBL_REQUIRES_INTERVENTION',
			'href' => 'index.php?parent=Settings&module=Wapro&view=List',
		];
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
		if (\App\Security\AdminAccess::isPermitted('Wapro')) {
			$links[] = \Vtiger_Link_Model::getInstanceFromValues([
				'linklabel' => 'LBL_WAPRO_ERP',
				'relatedModuleName' => 'Settings:Wapro',
				'linkicon' => 'fab fa-connectdevelop',
				'linkhref' => true,
				'linkurl' => 'index.php?parent=Settings&module=Wapro&view=List',
				'linkclass' => 'btn-primary',
				'showLabel' => 1,
			]);
		}
		return $links;
	}
}
