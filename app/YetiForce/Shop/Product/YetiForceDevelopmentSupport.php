<?php
/**
 * YetiForce shop DevelopmentSupport file.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
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
		'Micro' => 200,
		'Small' => 380,
		'Medium' => 700,
		'Large' => 1200,
		'Corporation' => 6000,
	];

	/** {@inheritdoc} */
	public $customPricesLabel = [
		'Micro' => 5,
		'Small' => 10,
		'Medium' => 20,
		'Large' => 40,
		'Corporation' => 200,
	];

	/** {@inheritdoc} */
	public $featured = true;

	/** {@inheritdoc} */
	public function verify($cache = true): bool
	{
		return true;
	}

	/** {@inheritdoc} */
	public function getPriceLabel($key): string
	{
		return $this->customPricesLabel[$key] . ' ' . \App\Language::translate('LBL_HOURS');
	}
}
