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
	/**
	 * {@inheritdoc}
	 */
	public $label = 'YetiForce Map';
	/**
	 * {@inheritdoc}
	 */
	public $prices = [
		'Micro' => 40,
		'Small' => 100,
		'Medium' => 200,
		'Large' => 400,
		'Corporation' => 2000,
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
