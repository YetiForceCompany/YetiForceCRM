<?php
/**
 * YetiForce shop ModulesPremium file.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\YetiForce\Shop\Product;

/**
 * YetiForce shop ModulesPremium class.
 */
class ModulesPremium extends \App\YetiForce\Shop\AbstractBaseProduct
{
	/**
	 * {@inheritdoc}
	 */
	public $prices = [
		'Micro' => 30,
		'Small' => 60,
		'Medium' => 100,
		'Large' => 250,
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
			if (isset($cacheData['ModulesPremium'])) {
				return $cacheData['ModulesPremium'];
			}
		}
		$status = true;
		if ((new \App\Db\Query())->from('w_#__servers')->where(['type' => 'Payments', 'status' => 1])->exists(\App\Db::getInstance('webservice'))) {
			$status = \App\YetiForce\Shop::check('ModulesPremium');
		}
		return $status;
	}
}
