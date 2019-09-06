<?php
/**
 * YetiForce shop InstallInHosting file.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\YetiForce\Shop\Product;

/**
 * YetiForce shop InstallInHosting class.
 */
class YetiForceInstallInHosting extends \App\YetiForce\Shop\AbstractBaseProduct
{
	/**
	 * {@inheritdoc}
	 */
	public $pricesType = 'selection';
	/**
	 * {@inheritdoc}
	 */
	public $prices = [
		'Micro' => 20,
		'Small' => 50,
		'Medium' => 100,
		'Large' => 250,
	];

	/**
	 * {@inheritdoc}
	 */
	public $customFields = [
		'subdomain' => [
			'label' => 'LBL_SHOP_DOMAIN_PREFIX',
			'type' => 'text',
			'validator' => 'required,custom[onlyLetterNumber]'
		],
		'email' => [
			'label' => 'LBL_EMAIL',
			'type' => 'email',
			'info' => 'LBL_EMAIL_INFO',
			'validator' => 'required,funcCall[Vtiger_Email_Validator_Js.invokeValidation]'
		]
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
