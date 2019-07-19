<?php
/**
 * YetiForce shop DisableFooter file.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\YetiForce\Shop\Product;

/**
 * YetiForce shop DisableFooter class.
 */
class DisableFooter extends \App\YetiForce\Shop\AbstractBaseProduct
{
	/**
	 * {@inheritdoc}
	 */
	public $prices = [
		's' => 5,
		'm' => 15,
		'l' => 30,
		'xl' => 50,
	];

	/**
	 * {@inheritdoc}
	 */
	public function verify(): bool
	{
		if (\App\Config::performance('LIMITED_INFO_IN_FOOTER')) {
			return \App\YetiForce\Shop::check('DisableFooter');
		}
		return true;
	}
}
