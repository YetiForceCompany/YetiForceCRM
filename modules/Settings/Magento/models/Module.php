<?php

/**
 * Magento Module Model Class.
 *
 * @package   Model
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Dudek <a.dudek@yetiforce.com>
 */
class Settings_Magento_Module_Model extends Settings_Vtiger_Module_Model
{
	/**
	 * Module Name.
	 *
	 * @var string
	 */
	public $name = 'Magento';

	/**
	 * Field form array.
	 *
	 * @var array[]
	 */
	public static $formFields = [
		'addressApi' => ['required' => 1],
		'username' => ['required' => 1, 'default' => ''],
		'password' => ['required' => 1, 'default' => ''],
		'masterSource' => ['required' => 1, 'values' => [], 'default' => 'magento', 'tooltip' => true],
		'storeCode' => ['required' => 1, 'default' => 'all'],
		'storeId' => ['required' => 1, 'default' => 1, 'min' => 1],
		'websiteId' => ['required' => 1, 'default' => 1, 'min' => 1],
		'customerLimit' => ['required' => 1, 'default' => 20, 'min' => 1],
		'productLimit' => ['required' => 1, 'default' => 20, 'min' => 1],
		'orderLimit' => ['required' => 1, 'default' => 20, 'min' => 1],
		'invoiceLimit' => ['required' => 1, 'default' => 20, 'min' => 1],
		'productImagesPath' => ['required' => 1, 'default' => 'media/catalog/product/', 'tooltip' => true],
		'storageId' => ['required' => 0, 'values' => [], 'default' => 0],
		'shippingServiceId' => ['required' => 0, 'default' => 0, 'min' => 0],
		'currencyId' => ['required' => 0, 'values' => [], 'default' => 0],
		'productMapClassName' => ['required' => 1, 'default' => '\App\Integrations\Magento\Synchronizator\Maps\Product', 'tooltip' => true],
		'invoiceMapClassName' => ['required' => 1, 'default' => '\App\Integrations\Magento\Synchronizator\Maps\Invoice', 'tooltip' => true],
		'orderMapClassName' => ['required' => 1, 'default' => '\App\Integrations\Magento\Synchronizator\Maps\Order', 'tooltip' => true],
		'customerMapClassName' => ['required' => 1, 'default' => '\App\Integrations\Magento\Synchronizator\Maps\Customer', 'tooltip' => true],
	];

	/**
	 * Return list fields in form.
	 *
	 * @return array[]
	 */
	public static function getFormFields(): array
	{
		return static::$formFields;
	}
}
