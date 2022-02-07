<?php

/**
 * Magento Module Model Class.
 *
 * @package   Settings.Model
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Dudek <a.dudek@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_Magento_Module_Model extends Settings_Vtiger_Module_Model
{
	/** {@inheritdoc} */
	public $name = 'Magento';

	/** {@inheritdoc} */
	public $baseTable = 'i_#__magento_servers';

	/** {@inheritdoc} */
	public $baseIndex = 'id';

	/** {@inheritdoc} */
	public $listFields = [
		'name' => 'LBL_NAME',
		'status' => 'LBL_STATUS',
		'url' => 'LBL_URL',
		'user_name' => 'LBL_USER_NAME',
	];

	/** {@inheritdoc} */
	public function getDefaultUrl()
	{
		return 'index.php?parent=Settings&module=Magento&view=List';
	}

	/** {@inheritdoc} */
	public function getCreateRecordUrl()
	{
		return 'index.php?parent=Settings&module=Magento&view=Edit';
	}

	/**
	 * Field form array.
	 *
	 * @var array[]
	 */
	public static $formFields = [
		'status' => ['required' => 0, 'purifyType' => 'Integer'],
		'name' => ['required' => 1, 'purifyType' => 'Text'],
		'url' => ['required' => 1, 'purifyType' => 'Url'],
		'user_name' => ['required' => 1, 'default' => '', 'purifyType' => 'Text'],
		'password' => ['required' => 1, 'default' => '', 'purifyType' => ''],
		'connector' => ['required' => 1, 'default' => 'Token', 'purifyType' => 'Standard'],
		'store_code' => ['required' => 1, 'default' => 'all', 'purifyType' => 'Alnum'],
		'store_id' => ['required' => 1, 'default' => 1, 'min' => 1, 'purifyType' => 'Integer'],
		'storage_id' => ['required' => 0, 'default' => 0, 'tooltip' => true, 'purifyType' => 'Integer'],
		'storage_quantity_location' => ['required' => 1,  'tooltip' => true, 'default' => 'Products',  'purifyType' => 'Text'],
		'shipping_service_id' => ['required' => 0, 'default' => 0, 'tooltip' => true, 'min' => 0, 'purifyType' => 'Integer'],
		'payment_paypal_service_id' => ['required' => 0, 'default' => 0, 'tooltip' => true, 'min' => 0, 'purifyType' => 'Integer'],
		'payment_cash_service_id' => ['required' => 0, 'default' => 0, 'tooltip' => true, 'min' => 0, 'purifyType' => 'Integer'],
		'sync_currency' => ['required' => 1, 'default' => true, 'tooltip' => true, 'purifyType' => 'Integer'],
		'sync_categories' => ['required' => 1, 'default' => true, 'tooltip' => true, 'purifyType' => 'Integer'],
		'sync_products' => ['required' => 1, 'default' => true, 'purifyType' => 'Integer'],
		'sync_customers' => ['required' => 1, 'default' => true, 'tooltip' => true, 'purifyType' => 'Integer'],
		'sync_orders' => ['required' => 1, 'default' => true, 'tooltip' => true, 'purifyType' => 'Integer'],
		'sync_invoices' => ['required' => 1, 'default' => true, 'tooltip' => true, 'purifyType' => 'Integer'],
		'categories_limit' => ['required' => 1, 'default' => 200, 'min' => 1, 'purifyType' => 'Text'],
		'products_limit' => ['required' => 1, 'default' => 1000, 'min' => 1, 'purifyType' => 'Text'],
		'customers_limit' => ['required' => 1, 'default' => 1000, 'min' => 1, 'purifyType' => 'Text'],
		'orders_limit' => ['required' => 1, 'default' => 200, 'min' => 1, 'purifyType' => 'Text'],
		'invoices_limit' => ['required' => 1, 'default' => 200, 'min' => 1, 'purifyType' => 'Text'],
		'product_map_class' => ['required' => 0, 'default' => '', 'tooltip' => true, 'purifyType' => 'ClassName'],
		'customer_map_class' => ['required' => 0, 'default' => '', 'tooltip' => true, 'purifyType' => 'ClassName'],
		'order_map_class' => ['required' => 0, 'default' => '', 'tooltip' => true, 'purifyType' => 'ClassName'],
		'invoice_map_class' => ['required' => 0, 'default' => '', 'tooltip' => true, 'purifyType' => 'ClassName'],
	];

	/**
	 * Return list fields in form.
	 *
	 * @return array[]
	 */
	public function getFormFields(): array
	{
		return static::$formFields;
	}

	/**
	 * Function to check if the functionality is enabled.
	 *
	 * @return bool
	 */
	public static function isActive(): bool
	{
		return 7 === (new \App\Db\Query())->from('vtiger_field')->where([
			'or',
			['tabid' => \App\Module::getModuleId('SSingleOrders'), 'fieldname' => 'magento_server_id'],
			['tabid' => \App\Module::getModuleId('SSingleOrders'), 'fieldname' => 'magento_id'],
			['tabid' => \App\Module::getModuleId('SSingleOrders'), 'fieldname' => 'status_magento'],
			['tabid' => \App\Module::getModuleId('FInvoice'), 'fieldname' => 'magento_server_id'],
			['tabid' => \App\Module::getModuleId('FInvoice'), 'fieldname' => 'magento_id'],
			['tabid' => \App\Module::getModuleId('ProductCategory'), 'fieldname' => 'magento_server_id'],
			['tabid' => \App\Module::getModuleId('ProductCategory'), 'fieldname' => 'magento_id'],
		])->count() && \App\EventHandler::checkActive('IStorages_RecalculateStockHandler_Handler', 'IStoragesAfterUpdateStock') && \App\Cron::checkActive('Vtiger_Magento_Cron');
	}
}
