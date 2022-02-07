<?php
/**
 * YetiForce shop Integrator file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\YetiForce\Shop\Product\Partner;

/**
 * YetiForce shop Integrator class.
 */
class Integrator extends \App\YetiForce\Shop\AbstractBaseProduct
{
	/** {@inheritdoc} */
	public $prices = [250];
	/** {@inheritdoc} */
	public $featured = true;
}
