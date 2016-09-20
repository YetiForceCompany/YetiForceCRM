<?php

/**
 * Basic Inventory Model Class
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_Inventory_Model
{

	var $name = false;

	/**
	 * Get invnetory instance
	 * @param string $moduleName Module name
	 * @return Vtiger_Inventory_Model instance
	 */
	public static function getInstance($moduleName)
	{
		$instance = Vtiger_Cache::get('Inventory', $moduleName);
		if (!$instance) {
			$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Inventory', $moduleName);
			$instance = new $modelClassName();
			$instance->initialize($moduleName);
			Vtiger_Cache::set('Inventory', $moduleName, $instance);
		}
		return $instance;
	}

	/**
	 * Initialize this instance
	 */
	public function initialize($name)
	{
		$this->name = $name;
	}

	protected static $discountsConfig = false;

	/**
	 * Get discounts configuration
	 * @return array config data
	 */
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

	/**
	 * Get global discounts list
	 * @return array discounts list
	 */
	public function getGlobalDiscounts()
	{
		$db = PearDatabase::getInstance();
		$config = [];
		$result = $db->pquery('SELECT * FROM a_yf_discounts_global WHERE status = ?', [0]);
		while ($row = $db->fetch_array($result)) {
			$config[$row['name']] = $row['value'];
		}
		return $config;
	}

	protected static $taxsConfig = false;

	/**
	 * Get tax configuration
	 * @return array config data
	 */
	public static function getTaxesConfig()
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

	/**
	 * Get global tax list
	 * @return array tax list
	 */
	public function getGlobalTaxs()
	{
		$db = PearDatabase::getInstance();
		$config = [];
		$result = $db->pquery('SELECT * FROM a_yf_taxes_global WHERE status = ?', [0]);
		while ($row = $db->fetch_array($result)) {
			$config[$row['name']] = $row['value'];
		}
		return $config;
	}

	/**
	 * Get discount from the account
	 * @param string $moduleName Module name
	 * @param int $record Record ID
	 * @return array
	 */
	public function getAccountDiscount($relatedRecord)
	{
		$discount = 0;
		$discountField = 'discount';
		$name = '';
		if (!empty($relatedRecord)) {
			$accountRecordModel = Vtiger_Record_Model::getInstanceById($relatedRecord);
			$discount = $accountRecordModel->get($discountField);
			$name = $accountRecordModel->getName();
		}
		return ['discount' => $discount, 'name' => $name];
	}

	/**
	 * Get tax from the account
	 * @param string $moduleName Module name
	 * @param int $record Record ID
	 * @return array
	 */
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
				$accountTaxs = Vtiger_Taxes_UIType::getValues($accountRecordModel->get($taxField));
				$name = $accountRecordModel->getName();
			}
		}

		return ['taxs' => $accountTaxs, 'name' => $name];
	}

	/**
	 * Active inventory blocks
	 * @param string $moduleName Module name
	 * @return string/false
	 */
	public function setInventoryTable($type)
	{
		$db = PearDatabase::getInstance();
		$moduleName = $this->name;

		$focus = CRMEntity::getInstance($moduleName);
		$basetable = $focus->table_name;
		$basetableid = $focus->table_index;

		$tableEnds = ['_inventory', '_invfield', '_invmap'];

		if ($type === true || $type === 'true') {
			$type = 1;
		} else {
			$type = 0;
		}
		$result = $db->update('vtiger_tab', [
			'type' => $type
			], 'name = ?', [$moduleName]
		);
		$i = 0;
		if ($result && $type) {
			while (isset($tableEnds[$i]) && $ends = $tableEnds[$i]) {
				switch ($ends) {
					case '_inventory':
						$sql = '(id int(19),seq int(10),KEY id (id),CONSTRAINT `fk_1_' . $basetable . $ends . '` FOREIGN KEY (`id`) REFERENCES `' . $basetable . '` (`' . $basetableid . '`) ON DELETE CASCADE)';
						break;
					case '_invfield':
						$sql = "(id int(19) AUTO_INCREMENT PRIMARY KEY, columnname varchar(30) NOT NULL, label varchar(50) NOT NULL, invtype varchar(30) NOT NULL,presence tinyint(1) unsigned NOT NULL DEFAULT '0',
					defaultvalue varchar(255),sequence int(10) unsigned NOT NULL, block tinyint(1) unsigned NOT NULL,displaytype tinyint(1) unsigned NOT NULL DEFAULT '1', params text, colspan tinyint(1) unsigned NOT NULL DEFAULT '1')";
						break;
					case '_invmap':
						$sql = '(module varchar(50) NOT NULL,field varchar(50) NOT NULL,tofield varchar(50) NOT NULL,PRIMARY KEY (`module`,`field`,`tofield`))';
						break;
				}
				if (!vtlib\Utils::CheckTable($basetable . $ends)) {
					vtlib\Utils::CreateTable($basetable . $ends, $sql, true);
				}
				$i++;
			}
		}
		return $result;
	}
}
