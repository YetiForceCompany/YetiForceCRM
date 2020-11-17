<?php
/**
 * YetiForce shop PremiumSupport file.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\YetiForce\Shop\Product;

/**
 * YetiForce shop PremiumSupport class.
 */
class YetiForceHelp extends \App\YetiForce\Shop\AbstractBaseProduct
{
	/** {@inheritdoc} */
	public $label = 'YetiForce Help';

	/** {@inheritdoc} */
	public $category = 'Support';

	/** {@inheritdoc} */
	public $website = 'https://yetiforce.com/en/marketplace/support';

	/** {@inheritdoc} */
	public $prices = [
		'Micro' => 25,
		'Small' => 50,
		'Medium' => 100,
		'Large' => 250,
		'Corporation' => 1250
	];
	/** {@inheritdoc} */
	public $featured = true;
}
