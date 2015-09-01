<?php

/**
 * Basic Inventory Model Class
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_Inventory_Model
{

	public static function getInstance($moduleName)
	{
		$instance = Vtiger_Cache::get('Inventory', $moduleName);
		if (!$instance) {
			$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Inventory', $moduleName);
			$instance = new $modelClassName();
			Vtiger_Cache::set('Inventory', $moduleName, $instance);
		}
		return $instance;
	}

	protected static $discountsConfig = false;

	public static function getDiscountsConfig()
	{
		if (self::$discountsConfig != false) {
			return self::$discountsConfig;
		}

		$db = PearDatabase::getInstance();
		$config = [];
		$result = $db->query('SELECT * FROM a_yf_discounts_config');
		while ($row = $db->fetch_array($result)) {
			$value = $row['value'];
			if (in_array($row['param'], ['discounts'])) {
				$value = explode(',', $value);
			}
			$config[$row['param']] = $value;
		}
		self::$discountsConfig = $config;
		return $config;
	}

	public function getGlobalDiscounts()
	{
		$db = PearDatabase::getInstance();
		$config = [];
		$result = $db->pquery('SELECT * FROM a_yf_discounts_global WHERE status = ?', [1]);
		while ($row = $db->fetch_array($result)) {
			$config[$row['name']] = $row['value'];
		}
		return $config;
	}

	protected static $taxsConfig = false;

	public static function getTaxsConfig()
	{
		if (self::$taxsConfig != false) {
			return self::$taxsConfig;
		}

		$db = PearDatabase::getInstance();
		$config = [];
		$result = $db->query('SELECT * FROM a_yf_taxes_config');
		while ($row = $db->fetch_array($result)) {
			$value = $row['value'];
			if (in_array($row['param'], ['taxs'])) {
				$value = explode(',', $value);
			}
			$config[$row['param']] = $value;
		}
		self::$taxsConfig = $config;
		return $config;
	}

	public function getGlobalTaxs()
	{
		$db = PearDatabase::getInstance();
		$config = [];
		$result = $db->pquery('SELECT * FROM a_yf_taxes_global WHERE status = ?', [1]);
		while ($row = $db->fetch_array($result)) {
			$config[$row['name']] = $row['value'];
		}
		return $config;
	}
	
	public function getAccountDiscount($moduleName, $record)
	{
		$inventoryField = Vtiger_InventoryField_Model::getInstance($moduleName);
		$accountField = $inventoryField->getReferenceField();
		$discount = 0;
		$discountField = 'discount';
		$name = '';
		if ($accountField) {
			$recordModel = Vtiger_Record_Model::getInstanceById($record, $moduleName);
			$relationFieldValue = $recordModel->get($accountField);
			if ($relationFieldValue != 0) {
				$accountRecordModel = Vtiger_Record_Model::getInstanceById($relationFieldValue);
				$discount = $accountRecordModel->get($discountField);
				$name = $accountRecordModel->getName();
			}
		}

		return ['discount' => $discount, 'name' => $name];
	}
	
	public function getAccountTax($moduleName, $record)
	{
		$inventoryField = Vtiger_InventoryField_Model::getInstance($moduleName);
		$accountField = $inventoryField->getReferenceField();
		$accountTaxs = [];
		$name = '';
		$taxField = Vtiger_InventoryField_Model::getTaxField('Accounts');
		if ($accountField != '' && $taxField != false) {
			$recordModel = Vtiger_Record_Model::getInstanceById($record, $moduleName);
			$relationFieldValue = $recordModel->get($accountField);
			if ($relationFieldValue != 0) {
				$accountRecordModel = Vtiger_Record_Model::getInstanceById($relationFieldValue, 'Accounts');
				$accountTaxs = Vtiger_Taxs_UIType::getValues($accountRecordModel->get($taxField));
				$name = $accountRecordModel->getName();
			}
		}

		return ['taxs' => $accountTaxs, 'name' => $name];
	}
}
