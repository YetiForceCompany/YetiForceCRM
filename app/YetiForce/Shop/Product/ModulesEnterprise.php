<?php
/**
 * YetiForce shop ModulesEnterprise file.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\YetiForce\Shop\Product;

/**
 * YetiForce shop ModulesEnterprise class.
 */
class ModulesEnterprise extends \App\YetiForce\Shop\AbstractBaseProduct
{
	/**
	 * {@inheritdoc}
	 */
	public $prices = [
		'Micro' => 100,
		'Small' => 200,
		'Medium' => 300,
		'Large' => 500,
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
			if (isset($cacheData['ModulesEnterprise'])) {
				return $cacheData['ModulesEnterprise'];
			}
		}
		$status = true;
		if ((new \App\Db\Query())->from('vtiger_tab')->where(['presence' => 0, 'premium' => 2])->exists()) {
			$status = \App\YetiForce\Shop::check('ModulesEnterprise');
		}
		return $status;
	}
}
