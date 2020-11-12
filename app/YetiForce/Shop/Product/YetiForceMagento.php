<?php
/**
 * YetiForce shop YetiForceMagento file.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
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
	public $website = 'https://yetiforce.com/en/yetiforce-magento-integration';

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
	public function verify($cache = true): bool
	{
		return true;
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
			'href' => 'index.php?parent=Settings&module=Magento&view=List'
		];
	}
}
