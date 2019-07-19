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
class PremiumSupport extends \App\YetiForce\Shop\AbstractBaseProduct
{
	/**
	 * {@inheritdoc}
	 */
	public $prices = [
		's' => 15,
		'm' => 25,
		'l' => 50,
		'xl' => 100,
	];

	/**
	 * {@inheritdoc}
	 */
	public function verify(): bool
	{
		return true;
	}
}
