<?php
/**
 * YetiForce shop PremiumModules file.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\YetiForce\Shop\Product;

/**
 * YetiForce shop PremiumModules class.
 */
class PremiumModules extends \App\YetiForce\Shop\AbstractBaseProduct
{
	/**
	 * {@inheritdoc}
	 */
	public $prices = [
		's' => 30,
		'm' => 60,
		'l' => 100,
		'xl' => 250,
	];

	/**
	 * {@inheritdoc}
	 */
	public function verify(): bool
	{
		return true;
	}
}
