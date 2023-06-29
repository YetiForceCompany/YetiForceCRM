<?php
/**
 * YetiForce shop YetiForceComarchXl file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\YetiForce\Shop\Product;

/**
 * YetiForce shop YetiForceComarchXl class.
 */
class YetiForceComarchXl extends \App\YetiForce\Shop\AbstractBaseProduct
{
	/** {@inheritdoc} */
	public $label = 'YetiForce Comarch ERP XL';

	/** {@inheritdoc} */
	public $category = 'Integrations';

	/** {@inheritdoc} */
	public $website = 'https://yetiforce.com/en/yetiforce-comarch-xl-integration-en';

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
		if (\App\YetiForce\Register::getProducts('YetiForceComarchXl')) {
			[$status, $message] = \App\YetiForce\Shop::checkWithMessage('YetiForceComarchXl');
		} else {
			$message = 'LBL_PAID_FUNCTIONALITY_ACTIVATED';
			$status = !(new \App\Db\Query())->from(\App\Integrations\Comarch::TABLE_NAME)
				->where(['type' => 1, 'status' => 1])->exists(\App\Db::getInstance('admin'));
		}
		return ['status' => $status, 'message' => $message];
	}

	/** {@inheritdoc} */
	public function analyzeConfiguration(): array
	{
		if (empty($this->expirationDate) || \Settings_Comarch_Activation_Model::check()) {
			return [];
		}
		return [
			'message' => \App\Language::translateArgs('LBL_FUNCTIONALITY_HAS_NOT_YET_BEEN_ACTIVATED', 'Settings:Comarch', 'Comarch'),
			'type' => 'LBL_REQUIRES_INTERVENTION',
			'href' => 'index.php?parent=Settings&module=Comarch&view=List',
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
		if (\App\Security\AdminAccess::isPermitted('Comarch')) {
			$links[] = \Vtiger_Link_Model::getInstanceFromValues([
				'linklabel' => 'LBL_COMARCH',
				'relatedModuleName' => 'Settings:Comarch',
				'linkicon' => 'fa-solid fa-cash-register',
				'linkhref' => true,
				'linkurl' => 'index.php?parent=Settings&module=Comarch&view=List',
				'linkclass' => 'btn-primary',
				'showLabel' => 1,
			]);
		}
		return $links;
	}
}
