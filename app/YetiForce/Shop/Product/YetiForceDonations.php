<?php
/**
 * YetiForce shop Donations file.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\YetiForce\Shop\Product;

/**
 * YetiForce shop Donations class.
 */
class YetiForceDonations extends \App\YetiForce\Shop\AbstractBaseProduct
{
	/**
	 * {@inheritdoc}
	 */
	public $pricesType = 'manual';
	/**
	 * {@inheritdoc}
	 */
	public $featured = true;

	/**
	 * {@inheritdoc}
	 */
	public function getPrice(): int
	{
		return \App\User::getNumberOfUsers();
	}

	/**
	 * {@inheritdoc}
	 */
	public function verify($cache = true): bool
	{
		return true;
	}
}
