<?php
/**
 * YetiForce shop YetiForceMagento file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\YetiForce\Shop\Product;

/**
 * YetiForce shop YetiForceMagento class.
 */
class YetiForceMagento extends \App\YetiForce\Shop\AbstractBaseProduct
{
	/** {@inheritdoc} */
	public $label = 'YetiForce Magento Integration';

	/** {@inheritdoc} */
	public $category = 'Integrations';

	/** {@inheritdoc} */
	public $website = 'https://yetiforce.com/en/yetiforce-magento-integration-en';

	/** {@inheritdoc} */
	public $prices = [
		'Micro' => 10,
		'Small' => 25,
		'Medium' => 50,
		'Large' => 100,
		'Corporation' => 500,
	];

	/** {@inheritdoc} */
	public $featured = true;

	/** {@inheritdoc} */
	public function verify(): array
	{
		$message = $status = true;
		if (\App\YetiForce\Register::getProducts('YetiForceMagento')) {
			[$status, $message] = \App\YetiForce\Shop::checkWithMessage('YetiForceMagento');
		} else {
			$message = 'LBL_PAID_FUNCTIONALITY_ACTIVATED';
			$status = !(new \App\Db\Query())->from('i_#__magento_servers')->exists(\App\Db::getInstance('admin'));
		}
		return ['status' => $status, 'message' => $message];
	}

	/** {@inheritdoc} */
	public function analyzeConfiguration(): array
	{
		if (empty($this->expirationDate) || \Settings_Magento_Module_Model::isActive()) {
			return [];
		}
		return [
			'message' => \App\Language::translateArgs('LBL_FUNCTIONALITY_HAS_NOT_YET_BEEN_ACTIVATED', 'Settings:Magento', 'Magento'),
			'type' => 'LBL_REQUIRES_INTERVENTION',
			'href' => 'index.php?parent=Settings&module=Magento&view=List',
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
