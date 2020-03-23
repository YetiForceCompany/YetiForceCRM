<?php
/**
 * YetiForce shop ModulesPartners file.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\YetiForce\Shop\Product;

/**
 * YetiForce shop ModulesPartners class.
 */
class ModulesPartners extends \App\YetiForce\Shop\AbstractBaseProduct
{
	/**
	 * {@inheritdoc}
	 */
	public $active = false;
	/**
	 * {@inheritdoc}
	 */
	public $prices = [
		'Micro' => 50,
		'Small' => 100,
		'Medium' => 190,
		'Large' => 300,
		'Corporation' => 1500,
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
		if ($cache) {
			$cacheData = \App\YetiForce\Shop::getFromCache();
			if (isset($cacheData['ModulesPartners'])) {
				return $cacheData['ModulesPartners'];
			}
		}
		$status = true;
		if ((new \App\Db\Query())->from('vtiger_tab')->where(['presence' => 0, 'premium' => 3])->exists()) {
			$status = \App\YetiForce\Shop::check('ModulesPartners');
		}
		return $status;
	}
}
