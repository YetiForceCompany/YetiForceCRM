<?php
/**
 * YetiForce shop DevelopmentSupport file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\YetiForce\Shop\Product;

/**
 * YetiForce shop DevelopmentSupport class.
 */
class YetiForceDevelopmentSupport extends \App\YetiForce\Shop\AbstractBaseProduct
{
	/** {@inheritdoc} */
	public $label = 'YetiForce Development';

	/** {@inheritdoc} */
	public $category = 'Support';

	/** {@inheritdoc} */
	public $pricesType = 'selection';

	/** {@inheritdoc} */
	public $website = 'https://yetiforce.com/en/marketplace/development-support';

	/** {@inheritdoc} */
	public $prices = [
		'Micro' => 225,
		'Small' => 432,
		'Medium' => 828,
		'Large' => 1611,
		'Corporation' => 3780,
		'ExtraLarge' => 7200,
	];

	/** {@inheritdoc} */
	public $customPricesLabel = [
		'Micro' => 5,
		'Small' => 10,
		'Medium' => 20,
		'Large' => 40,
		'Corporation' => 100,
		'ExtraLarge' => 200,
	];

	/** {@inheritdoc} */
	public $featured = true;

	/** {@inheritdoc} */
	public function getPriceLabel($key): string
	{
		return $this->customPricesLabel[$key] . ' ' . \App\Language::translate('LBL_HOURS');
	}

	/** {@inheritdoc} */
	public function getAdditionalButtons(): array
	{
		return [
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
	}
}
