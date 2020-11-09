<?php
/**
 * YetiForce shop YetiForcePlGus file.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\YetiForce\Shop\Product;

/**
 * YetiForce shop YetiForcePlGus class.
 */
class YetiForcePlGus extends \App\YetiForce\Shop\AbstractBaseProduct
{
	/** {@inheritdoc} */
	public $label = 'YetiForce GUS';
	/** {@inheritdoc} */
	public $category = 'Integrations';
	/** {@inheritdoc} */
	public $website = 'https://yetiforce.com/en/yetiforce-gus';
	/** {@inheritdoc} */
	public $prices = [
		'Micro' => 5,
		'Small' => 12,
		'Medium' => 25,
		'Large' => 50,
		'Corporation' => 100,
	];

	/** {@inheritdoc} */
	public $featured = true;

	/** {@inheritdoc} */
	public function verify($cache = true): bool
	{
		return true;
	}
}
