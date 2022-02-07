<?php
/**
 * YetiForce shop ModuleProducer file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\YetiForce\Shop\Product\Partner;

/**
 * YetiForce shop ModuleProducer class.
 */
class ModuleProducer extends \App\YetiForce\Shop\AbstractBaseProduct
{
	/** {@inheritdoc} */
	public $prices = [100];
	/** {@inheritdoc} */
	public $featured = true;
}
