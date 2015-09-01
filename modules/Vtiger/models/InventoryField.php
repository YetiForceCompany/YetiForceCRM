<?php

/**
 * Basic Inventory Model Class
 * @package YetiForce.Inventory
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_InventoryField_Model extends Vtiger_Base_Model
{

	protected static $fields = false;
	protected static $columns = false;
	protected $jsonFields = ['discountparam', 'taxparam'];

	/**
	 * Create the name of the Inventory data table
	 * @param string $module Module name
	 * @param string $prefix Prefix table
	 * @return string Table name
	 */
	public function getTableName($type = 'data')
	{
		switch ($type) {
			case 'data':
				$prefix = '_inventory';
				break;
			case 'fields':
				$prefix = '_invfield';
				break;
			case 'autofield':
				$prefix = '_invmap';
				break;
		}
		$moduleName = strtolower($this->get('module'));
		$basetable = 'vtiger_' . $moduleName;
		$supfield = $basetable . $prefix;
		return $supfield;
	}

	/**
	 * Loading the Inventory data
	 * @param string $module Module name
	 * @param boolean $returnInBlock Should the result be divided into blocks
	 * @return array Inventory data
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
		} else {
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
			$discountsConfig = Vtiger_Inventory_Model::getDiscountsConfig();
			if ($discountsConfig['active'] == '0') {
				return false;
			}
		}

		return true;
	}

	/**
	 * Loading the Inventory data
	 * @param string $module Module name
	 * @param boolean $returnInBlock Should the result be divided into blocks
	 * @return array Inventory data
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
	 * @return \modelClassName Instance Vtiger_Basic_InventoryField
	 */
	public function getInventoryFieldInstance($valueArray)
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . '| ');

		$className = Vtiger_Loader::getComponentClassName('InventoryField', $valueArray['invtype'], $this->get('module'));
		$instance = new $className();
		$instance->initialize($valueArray);

		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__);
		return $instance;
	}

	/**
	 * Retrieve list of all fields
	 * @param string $module Module name
	 * @return array Fields instance Vtiger_Basic_InventoryField
	 */
	public static function getAllFields($module = false)
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . '| ' . $module);
		$fieldPaths = ['modules/Vtiger/inventoryfields/'];
		if ($module) {
			$fieldPaths[] = "modules/$module/inventoryfields/";
		}
		$fields = [];
		foreach ($fieldPaths as $fieldPath) {
			if (!is_dir($fieldPath))
				continue;
			foreach (new DirectoryIterator($fieldPath) as $fileinfo) {
				if ($fileinfo->isFile() && $fileinfo->getFilename() != 'Basic.php') {
					$fieldName = str_replace('.php', '', $fileinfo->getFilename());
					$className = Vtiger_Loader::getComponentClassName('InventoryField', $fieldName, $this->get('module'));
					$fields[$fieldName] = new $className();
				}
			}
		}
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__);
		return $fields;
	}

	/**
	 * Retrieve list of parameters
	 * @param array $fields Array of instances fields (Vtiger_Basic_InventoryField)
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

	public function getAutoCompleteField($moduleName)
	{
		$db = PearDatabase::getInstance();
		$table = $this->getTableName('autofield');
		$result = $db->query("SHOW TABLES LIKE '$table'");
		if ($result->rowCount() == 0) {
			return false;
		}
		$result = $db->pquery('SELECT * FROM ' . $table . ' WHERE module = ?', [$moduleName]);
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

	public function getReferenceField($mainModule = 'Accounts')
	{
		$relationField = $this->get('relationField' . $mainModule);
		if (!$relationField) {
			$moduleModel = Vtiger_Module_Model::getInstance($this->get('module'));
			$modelFields = $moduleModel->getFields();
			$relationField = false;
			foreach ($modelFields as $fieldName => $fieldModel) {
				if ($fieldModel->getFieldDataType() == Vtiger_Field_Model::REFERENCE_TYPE) {
					$referenceList = $fieldModel->getReferenceList();
					if (in_array($mainModule, $referenceList)) {
						$relationField = $fieldName;
						break;
					}
				}
			}
		}
		return $relationField;
	}

	public function isWysiwygType($moduleName)
	{
		if (!$moduleName) {
			return false;
		}
		$cache = Vtiger_Cache::get('InventoryIsWysiwygType', $moduleName);
		if ($cache) {
			return $cache;
		}
		$return = 0;
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$fieldModel = Vtiger_Field_Model::getInstance('description', $moduleModel);
		if ($fieldModel && $fieldModel->get('uitype') == '300') {
			$return = 1;
		}
		Vtiger_Cache::set('InventoryIsWysiwygType', $moduleName, $return);
		return $return;
	}

	public function getTaxField($moduleName)
	{
		$cache = Vtiger_Cache::get('InventoryIsGetTaxField', $moduleName);
		if ($cache) {
			return $cache;
		}
		$return = false;
		if ($moduleName == '') {
			return $return;
		}
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		foreach ($moduleModel->getFields() as $fieldName => $fieldModel) {
			if ($fieldModel->get('uitype') == 303) {
				$return = $fieldName;
				continue;
			}
		}

		Vtiger_Cache::set('InventoryIsGetTaxField', $moduleName, $return);
		return $return;
	}

	public function getValueForSave(Vtiger_Request $request, $field, $i)
	{
		$value = '';
		if (in_array($field, $this->jsonFields) && $request->get($field . $i) != '') {
			$value = json_encode($request->get($field . $i));
		} else if ($request->has($field . $i)) {
			$value = $request->get($field . $i);
		} else if ($request->has($field)) {
			$value = $request->get($field);
		}
		if (in_array($field, ['price', 'gross', 'net', 'discount', 'purchase', 'margin', 'marginp', 'tax', 'total'])) {
			$value = CurrencyField::convertToDBFormat($value, null, true);
		}
		return $value;
	}

	public function addField($type, $params)
	{
		$adb = PearDatabase::getInstance();
		$inventoryClassName = Vtiger_Loader::getComponentClassName('InventoryField', $type, $this->get('module'));
		$instance = new $inventoryClassName();
		$table = $this->getTableName();
		$columnName = $instance->getColumnName();
		$label = $instance->getDefaultLabel();
		$defaultValue = $instance->getDefaultValue();
		$colSpan = $instance->getColSpan();
		if (isset($params['column'])) {
			$columnName = $params['column'];
		}
		if (isset($params['label'])) {
			$label = $params['label'];
		}
		if (isset($params['defaultValue'])) {
			$defaultValue = $params['defaultValue'];
		}
		if (isset($params['colSpan'])) {
			$colSpan = $params['colSpan'];
		}
		
		Vtiger_Utils::AddColumn($table, $columnName, $instance->getDBType());
		foreach ($instance->getCustomColumn() as $column => $criteria) {
			Vtiger_Utils::AddColumn($table, $column, $criteria);
		}
		$result = $adb->query("SELECT MAX(sequence) AS max FROM " . $this->getTableName('fields'));
		$sequence = (int) $adb->getSingleValue($result) + 1;

		$adb->insert($this->getTableName('fields'), [
			'columnname' => $columnName,
			'label' => $label,
			'invtype' => $instance->getName(),
			'defaultvalue' => $defaultValue,
			'sequence' => $sequence,
			'block' => $params['block'],
			'displaytype' => $params['displayType'],
			'params' => $params['params'],
			'colspan' => $colSpan,
		]);
	}
}
