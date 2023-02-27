<?php
/**
 * Module file for WooCommerce integration model.
 *
 * @package   Settings.Model
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
/**
 * Module class for WooCommerce integration model.
 */
class Settings_WooCommerce_Module_Model extends Settings_Vtiger_Module_Model
{
	/** {@inheritdoc} */
	public $name = 'WooCommerce';
	/** {@inheritdoc} */
	public $baseTable = \App\Integrations\WooCommerce::TABLE_NAME;
	/** {@inheritdoc} */
	public $baseIndex = 'id';
	/** {@inheritdoc} */
	public $listFields = [
		'name' => 'LBL_NAME',
		'status' => 'LBL_STATUS',
		'url' => 'LBL_URL',
		'user_name' => 'LBL_USER_NAME',
	];
	/** @var array[] Field form array. */
	public static $formFields = [
		'status' => ['required' => 0, 'purifyType' => 'Integer'],
		'name' => ['required' => 1, 'purifyType' => 'Text'],
		'url' => ['required' => 1, 'purifyType' => 'Url'],
		'user_name' => ['required' => 1, 'default' => '', 'purifyType' => 'Text'],
		'password' => ['required' => 1, 'default' => '', 'purifyType' => ''],
		'connector' => ['required' => 1, 'default' => 'HttpAuth', 'purifyType' => 'Standard'],
		'verify_ssl' => ['required' => 1, 'default' => 1, 'purifyType' => 'Integer'],
		'master' => ['required' => 1, 'default' => 0, 'purifyType' => 'Integer'],
		'assigned_user_id' => ['required' => 1, 'purifyType' => 'Integer'],
		'sync_currency' => ['required' => 1, 'default' => true, 'tooltip' => true, 'purifyType' => 'Integer'],
		'sync_categories' => ['required' => 1, 'default' => true, 'tooltip' => true, 'purifyType' => 'Integer'],
		'direction_categories' => ['required' => 1, 'default' => 0, 'purifyType' => 'Integer'],
		'sync_tags' => ['required' => 1, 'default' => true, 'tooltip' => true, 'purifyType' => 'Integer'],
		'direction_tags' => ['required' => 1, 'default' => 0, 'purifyType' => 'Integer'],
		'sync_products' => ['required' => 1, 'default' => true, 'purifyType' => 'Integer'],
		'direction_products' => ['required' => 1, 'default' => 0, 'purifyType' => 'Integer'],
		'sync_orders' => ['required' => 1, 'default' => true, 'tooltip' => true, 'purifyType' => 'Integer'],
		'direction_orders' => ['required' => 1, 'default' => 0, 'purifyType' => 'Integer'],
		'shipping_service_id' => ['required' => 0, 'default' => 0, 'tooltip' => true, 'min' => 0, 'purifyType' => 'Integer'],
		'products_limit' => ['required' => 1, 'default' => 100, 'min' => 1, 'purifyType' => 'Text'],
		'orders_limit' => ['required' => 1, 'default' => 50, 'min' => 1, 'purifyType' => 'Text'],
	];

	/** {@inheritdoc} */
	public function getDefaultUrl()
	{
		return 'index.php?parent=Settings&module=WooCommerce&view=List';
	}

	/** {@inheritdoc} */
	public function getCreateRecordUrl()
	{
		return 'index.php?parent=Settings&module=WooCommerce&view=Edit';
	}

	/**
	 * Return list fields in form.
	 *
	 * @return array[]
	 */
	public function getFormFields(): array
	{
		return static::$formFields;
	}
}
