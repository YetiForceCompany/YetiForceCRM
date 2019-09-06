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
	/**
	 * {@inheritdoc}
	 */
	public $pricesType = 'selection';
	/**
	 * {@inheritdoc}
	 */
	public $prices = [
		'Micro' => 200,
		'Small' => 380,
		'Medium' => 700,
		'Large' => 1200,
	];
	/**
	 * {@inheritdoc}
	 */
	public $featured = true;

	/**
	 * {@inheritdoc}
	 */
	public function verify($cache = true): bool
	{
		return true;
	}
}
