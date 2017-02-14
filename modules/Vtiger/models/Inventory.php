<?php

/**
 * Basic Inventory Model Class
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_Inventory_Model
{

	public $name = false;

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

	/**
	 * Get discounts configuration
	 * @return array config data
	 */
	public static function getDiscountsConfig()
	{
		if (\App\Cache::has('Inventory', 'DiscountConfiguration')) {
			return \App\Cache::get('Inventory', 'DiscountConfiguration');
		}
		$config = [];
		$dataReader = (new \App\Db\Query())->from('a_#__discounts_config')->createCommand(\App\Db::getInstance('admin'))->query();
		while ($row = $dataReader->read()) {
			$value = $row['value'];
			if (in_array($row['param'], ['discounts'])) {
				$value = explode(',', $value);
			}
			$config[$row['param']] = $value;
		}
		\App\Cache::save('Inventory', 'DiscountConfiguration', $config, \App\Cache::LONG);
		return $config;
	}

	/**
	 * Get global discounts list
	 * @return array discounts list
	 */
	public function getGlobalDiscounts()
	{
		if (\App\Cache::has('Inventory', 'Discounts')) {
			return \App\Cache::get('Inventory', 'Discounts');
		}
		$discounts = (new App\Db\Query())->from('a_#__discounts_global')->where(['status' => 0])
				->createCommand(App\Db::getInstance('admin'))->queryAllByGroup(1);
		\App\Cache::save('Inventory', 'Discounts', $discounts, \App\Cache::LONG);
		return $discounts;
	}

	/**
	 * Get tax configuration
	 * @return array config data
	 */
	public static function getTaxesConfig()
	{
		if (\App\Cache::has('Inventory', 'TaxConfiguration')) {
			return \App\Cache::get('Inventory', 'TaxConfiguration');
		}
		$config = [];
		$dataReader = (new App\Db\Query())->from('a_#__taxes_config')->createCommand(App\Db::getInstance('admin'))->query();
		while ($row = $dataReader->read()) {
			$value = $row['value'];
			if (in_array($row['param'], ['taxs'])) {
				$value = explode(',', $value);
			}
			$config[$row['param']] = $value;
		}
		\App\Cache::save('Inventory', 'TaxConfiguration', $config, \App\Cache::LONG);
		return $config;
	}

	/**
	 * Get global tax list
	 * @return array tax list
	 */
	public static function getGlobalTaxes()
	{
		if (\App\Cache::has('Inventory', 'Taxes')) {
			return \App\Cache::get('Inventory', 'Taxes');
		}
		$taxes = (new App\Db\Query())->from('a_#__taxes_global')->where(['status' => 0])
				->createCommand(App\Db::getInstance('admin'))->queryAllByGroup(1);
		\App\Cache::save('Inventory', 'Taxes', $taxes, \App\Cache::LONG);
		return $taxes;
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
	 * @param int/bool $type
	 * @return bool/self
	 */
	public function setMode($type)
	{
		$db = \App\Db::getInstance();
		$moduleName = $this->name;

		$result = $db->createCommand()->update('vtiger_tab', ['type' => (int) $type], ['name' => $moduleName])->execute();
		if (!$result) {
			return false;
		}
		if ($type) {
			$this->createInventoryTables();
		}
		return $this;
	}

	/**
	 * Create inventory tables
	 */
	public function createInventoryTables()
	{
		$db = \App\Db::getInstance();
		$focus = CRMEntity::getInstance($this->name);
		$moduleLowerCase = strtolower($this->name);
		$basetable = $focus->table_name;
		$importer = new \App\Db\Importers\Base();
		$tables = [
			'_inventory' => [
				'columns' => [
					'id' => $importer->integer(11),
					'seq' => $importer->integer(10),
				],
				'index' => [
						[$moduleLowerCase . '_inventory_idx', 'id'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'_invfield' => [
				'columns' => [
					'id' => $importer->primaryKey(),
					'columnname' => $importer->stringType(30)->notNull(),
					'label' => $importer->stringType(50)->notNull(),
					'invtype' => $importer->stringType(30)->notNull(),
					'presence' => $importer->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'defaultvalue' => $importer->stringType(),
					'sequence' => $importer->integer(10)->unsigned()->notNull(),
					'block' => $importer->smallInteger(1)->unsigned()->notNull(),
					'displaytype' => $importer->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
					'params' => $importer->text(),
					'colspan' => $importer->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'_invmap' => [
				'columns' => [
					'module' => $importer->stringType(50)->notNull(),
					'field' => $importer->stringType(50)->notNull(),
					'tofield' => $importer->stringType(50)->notNull(),
				],
				'primaryKeys' => [
						[$moduleLowerCase . '_invmap_pk', ['module', 'field', 'tofield']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
		]];
		$base = new \App\Db\Importer();
		$base->dieOnError = AppConfig::debug('SQL_DIE_ON_ERROR');
		foreach ($tables as $postFix => $data) {
			$tableName = $basetable . $postFix;
			if (!$db->isTableExists($tableName)) {
				$importer->tables = [$tableName => $data];
				$base->addTables($importer);
			}
		}
	}
}
