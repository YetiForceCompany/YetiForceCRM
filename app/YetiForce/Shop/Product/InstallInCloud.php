<?php
/**
 * YetiForce shop InstallInCloud file.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\YetiForce\Shop\Product;

/**
 * YetiForce shop InstallInCloud class.
 */
class InstallInCloud extends \App\YetiForce\Shop\AbstractBaseProduct
{
	/**
	 * {@inheritdoc}
	 */
	public $prices = [
		'Micro' => 15,
		'Small' => 25,
		'Medium' => 50,
		'Large' => 100,
	];

	/**
	 * {@inheritdoc}
	 */
	public $customFields = [
		'subdomain' => ['type' => 'text'],
		'email' => ['type' => 'email'],
	];

	/**
	 * {@inheritdoc}
	 */
	public $companyDataForm = false;

	/**
	 * {@inheritdoc}
	 */
	public $featured = true;

	/**
	 * {@inheritdoc}
	 */
	public function verify($cache = true): bool
	{
		return true;
	}
}
