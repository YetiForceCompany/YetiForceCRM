<?php
/**
 * YetiForce shop YetiForceMap file.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\YetiForce\Shop\Product;

/**
 * YetiForce shop YetiForceMap class.
 */
class YetiForceMap extends \App\YetiForce\Shop\AbstractBaseProduct
{
	/** {@inheritdoc} */
	public $label = 'YetiForce Map';
	/** {@inheritdoc} */
	public $category = 'Addons';
	/** {@inheritdoc} */
	public $website = 'https://yetiforce.com/en/yetiforce-map';
	/** {@inheritdoc} */
	public $prices = [
		'Micro' => 20,
		'Small' => 50,
		'Medium' => 100,
		'Large' => 250,
		'Corporation' => 1250,
	];

	/** {@inheritdoc} */
	public $featured = true;

	/** {@inheritdoc} */
	public function verify($cache = true): bool
	{
		return true;
	}
}
