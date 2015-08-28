<?php

/**
 * Basic Inventory Field Model Class
 * @package YetiForce.Inventory
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_InventoryField_Model extends Vtiger_Base_Model
{

	const DATA_PREFIX = '_sups';
	const FIELDS_PREFIX = '_supfield';
	const AUTOFIELD_PREFIX = '_supmap';

	protected static $fields = false;
	protected static $columns = false;

	/**
	 * Create the name of the Supplies data table
	 * @param string $module Module name
	 * @param string $prefix Prefix table
	 * @return string Table name
	 */
	public function getTableName($type = 'data')
	{
		switch ($type) {
			case 'data':
				$prefix = self::DATA_PREFIX;
				break;
			case 'fields':
				$prefix = self::FIELDS_PREFIX;
				break;
			case 'autofield':
				$prefix = self::AUTOFIELD_PREFIX;
				break;
		}
		$moduleName = strtolower($this->get('module'));
		$basetable = 'vtiger_' . $moduleName;
		$supfield = $basetable . $prefix;
		return $supfield;
	}

	/**
	 * Loading the Supplies data
	 * @param string $module Module name
	 * @param boolean $returnInBlock Should the result be divided into blocks
	 * @return array Supplies data
	 */
	public function getFields($returnInBlock = false)
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . '| ');

		if (!$this->fields) {
			$db = PearDatabase::getInstance();
			$table = $this->getTableName('fields');
			$result = $db->query("SHOW TABLES LIKE '$table'");
			if ($result->rowCount() == 0) {
				return false;
			}
			$result = $db->pquery('SELECT * FROM ' . $table . ' WHERE presence = ? ORDER BY sequence', [0]);
			$fields = [];
			while ($row = $db->fetch_array($result)) {
				if (!$this->isActiveField($row)) {
					continue;
				}
				$fields[$row['columnname']] = $this->getInventoryFieldInstance($row);
			}
			$this->fields = $fields;
		}else{
			$fields = $this->fields;
		}
		if ($returnInBlock) {
			$block = [];
			foreach ($fields as $field) {
				$block[$field->get('block')][$field->get('columnname')] = $field;
			}
			$fields = $block;
		}
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__);
		return $fields;
	}

	public function isActiveField($row)
	{
		if (in_array($row['suptype'], ['Discount', 'DiscountMode'])) {
			$discountsConfig = Products_Record_Model::getDiscountsConfig();
			if ($discountsConfig['active'] == '0') {
				return false;
			}
		}

		return true;
	}

	/**
	 * Loading the Supplies data
	 * @param string $module Module name
	 * @param boolean $returnInBlock Should the result be divided into blocks
	 * @return array Supplies data
	 */
	public function getColumns()
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . '| ');
		if ($this->columns) {
			return $this->columns;
		}

		$columns = [];
		foreach ($this->getFields() as $field) {
			$column = $field->getColumnName();
			if ($column != '')
				$columns[] = $column;
			foreach ($field->getCustomColumn() as $name => $field) {
				$columns[] = $name;
			}
		}
		$this->columns = $columns;
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__);
		return $columns;
	}

	/**
	 * Creating installation of the field from the table
	 * @param string $valueArray Array of data
	 * @return \modelClassName Instance Supplies_Basic_Field
	 */
	public function getInventoryFieldInstance($valueArray)
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . '| ');

		$className = Vtiger_Loader::getComponentClassName('InnventoryField', $valueArray['suptype'], $this->get('module'));
		$instance = new $className();
		$instance->initialize($valueArray);

		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__);
		return $instance;
	}

	/**
	 * Retrieve list of all fields
	 * @param string $module Module name
	 * @return array Fields instance Supplies_Basic_Field
	 */
	public static function getAllFields($module = false)
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . '| ' . $module);
		$fieldPaths = ['modules/Supplies/fields/'];
		if ($module) {
			$fieldPaths[] = "modules/$module/fields/";
		}
		$fields = [];
		foreach ($fieldPaths as $fieldPath) {
			if (!is_dir($fieldPath))
				continue;
			foreach (new DirectoryIterator($fieldPath) as $fileinfo) {
				if ($fileinfo->isFile() && $fileinfo->getFilename() != 'Basic.php') {
					$fieldName = str_replace('.php', '', $fileinfo->getFilename());
					$className = Vtiger_Loader::getComponentClassName('Field', $fieldName, 'Supplies');
					$fields[$fieldName] = new $className();
				}
			}
		}
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__);
		return $fields;
	}

	/**
	 * Retrieve list of parameters
	 * @param array $fields Array of instances fields (Supplies_Basic_Field)
	 * @return array Array of parameters
	 */
	public static function getMainParams($fields)
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__);

		$params = false;
		foreach ($fields as $field) {
			if ($field->getName() == 'Name') {
				$params = Zend_Json::decode($field->get('params'));
			}
		}

		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__);
		return $params;
	}

	public static function getInstance($moduleName)
	{
		$instance = Vtiger_Cache::get('inventoryField', $moduleName);
		if (!$instance) {
			$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'InventoryField', $moduleName);
			$instance = new $modelClassName();
			$instance->set('module', $moduleName);
			Vtiger_Cache::set('inventoryField', $moduleName, $instance);
		}
		return $instance;
	}

	public static function getAutoCompleteField($recordModuleName, $moduleName)
	{
		$db = PearDatabase::getInstance();
		$table = self::getTableName($moduleName, 'autofield');
		$result = $db->query("SHOW TABLES LIKE '$table'");
		if ($result->rowCount() == 0) {
			return false;
		}
		$result = $db->pquery('SELECT * FROM ' . $table . ' WHERE module = ?', [$recordModuleName]);
		$fields = [];
		while ($row = $db->fetch_array($result)) {
			$fields[] = $row;
		}
		return $fields;
	}

	public static function getTaxParam($taxParam, $net, $return = false)
	{
		$taxParam = json_decode($taxParam, true);
		if (count($taxParam) == 0) {
			return [];
		}
		if (is_string($taxParam['aggregationType'])) {
			$taxParam['aggregationType'] = [$taxParam['aggregationType']];
		}
		if (!$return) {
			$return = [];
		}
		foreach ($taxParam['aggregationType'] as $aggregationType) {
			$precent = $taxParam[$aggregationType . 'Tax'];
			$return[$precent] += $net * ($precent / 100);
		}
		return $return;
	}
}
