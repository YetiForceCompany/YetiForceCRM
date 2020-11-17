<?php
/**
 * YetiForce shop ModuleProducer file.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
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
