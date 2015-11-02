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
	protected $jsonFields = ['discountparam', 'taxparam', 'currencyparam'];

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
		$focus = CRMEntity::getInstance($this->get('module'));
		$basetable = $focus->table_name;
		$supfield = $basetable . $prefix;
		return $supfield;
	}

	/**
	 * Loading the Inventory data
	 * @param boolean $returnInBlock Should the result be divided into blocks
	 * @param array $ids
	 * @return array Inventory data
	 */
	public function getFields($returnInBlock = false, $ids = [], $viewType = false)
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . '| ');
		$key = $returnInBlock ? 'block' : 'noBlock';
		if (!$this->fields[$key]) {
			$db = PearDatabase::getInstance();
			$table = $this->getTableName('fields');
			$result = $db->query("SHOW TABLES LIKE '$table'");
			if ($result->rowCount() == 0) {
				return false;
			}
			$where = 'presence = ?';
			$params = [0];
			if ($ids) {
				$where = '`id` IN (' . generateQuestionMarks($ids) . ')';
				$params = $ids;
			}
			$result = $db->pquery('SELECT * FROM ' . $table . ' WHERE ' . $where . ' ORDER BY sequence', $params);
			$fields = [];
			while ($row = $db->fetch_array($result)) {
				if (!$this->isActiveField($row)) {
					continue;
				}
				$inventoryFieldInstance = $this->getInventoryFieldInstance($row);
				if ($viewType == 'Detail' && !$inventoryFieldInstance->isVisible()) {
					continue;
				}
				if ($returnInBlock) {
					$fields[$row['block']][$row['columnname']] = $inventoryFieldInstance;
				} else {
					$fields[$row['columnname']] = $inventoryFieldInstance;
				}
			}
			$this->fields[$key] = $fields;
		} else {
			$fields = $this->fields[$key];
		}

		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__);
		return $fields;
	}

	/**
	 * Check whether this field is active
	 * @param array $row Field entry from the database
	 * @return boolean
	 */
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
	 * Get inventory columns
	 * @param string $module Module name
	 * @param boolean $returnInBlock Should the result be divided into blocks
	 * @return array Inventory columns
	 */
	public function getColumns()
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . '| ');
		if ($this->columns) {
			return $this->columns;
		}

		$columns = [];
		foreach ($this->getFields() as $key => $field) {
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
		} else {
			$module = 'Vtiger';
		}
		$fields = [];
		foreach ($fieldPaths as $fieldPath) {
			if (!is_dir($fieldPath))
				continue;
			foreach (new DirectoryIterator($fieldPath) as $fileinfo) {
				if ($fileinfo->isFile() && $fileinfo->getFilename() != 'Basic.php') {
					$fieldName = str_replace('.php', '', $fileinfo->getFilename());
					$className = Vtiger_Loader::getComponentClassName('InventoryField', $fieldName, $module);
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
		if (isset($fields)) {
			foreach ($fields as $field) {
				if ($field->getName() == 'Name') {
					$params = Zend_Json::decode($field->get('params'));
				}
			}
		}
		if (is_string($params['modules'])) {
			$params['modules'] = [$params['modules']];
		}
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__);
		return $params;
	}

	/**
	 * Get Vtiger_InventoryField_Model instance
	 * @param string $moduleName Module name
	 * @return \modelClassName Vtiger_InventoryField_Model Instance
	 */
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

	/**
	 * Get fields to auto-complete
	 * @param string $moduleName
	 * @return array
	 */
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

	/**
	 * Get configuration parameters for taxes
	 * @param string $taxParam String parameters json encode
	 * @param int $net net price
	 * @param array $return
	 * @return array
	 */
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

	/**
	 * Get related field name
	 * @param string $mainModule Module Name
	 * @return string
	 */
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

	/**
	 * Whether the module should be turned on Wysiwyg
	 * @param string $moduleName Module Name
	 * @return boolean|int
	 */
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

	/**
	 * Get field name for the module taxes
	 * @param string $moduleName Module name
	 * @return string Tax field name
	 */
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

	/**
	 * Get the value to save
	 * @param Vtiger_Request $request
	 * @param string $field Field name
	 * @param int $i Sequence number
	 * @return string
	 */
	public function getValueForSave(Vtiger_Request $request, $field, $i)
	{
		$value = '';
		if ($request->has($field . $i)) {
			$value = $request->get($field . $i);
		} else if ($request->has($field)) {
			$value = $request->get($field);
		}

		if (in_array($field, $this->jsonFields) && $value != '') {
			$value = json_encode($value);
		}
		if (in_array($field, ['price', 'gross', 'net', 'discount', 'purchase', 'margin', 'marginp', 'tax', 'total'])) {
			$value = CurrencyField::convertToDBFormat($value, null, true);
		}
		return $value;
	}

	/**
	 * Creating a new field
	 * @param string $type
	 * @param array $params
	 * @return array/false
	 */
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
		if (!$instance->isOnlyOne()) {
			$id = $this->getUniqueID($instance);
			$columnName = $columnName . $id;
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

		return $adb->insert($this->getTableName('fields'), [
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

	/**
	 * Save field value
	 * @param array $param
	 * @return string/false
	 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
	 */
	public function saveField($param)
	{
		$adb = PearDatabase::getInstance();
		$columns = ['label', 'invtype', 'defaultValue', 'sequence', 'block', 'displayType', 'params', 'colSSpan'];
		$set = [];
		$params = [];
		foreach ($columns AS $columnName) {
			if (isset($param[$columnName])) {
				$set[] = '`' . strtolower($columnName) . '`';
				$params[] = $param[$columnName];
			}
		}
		$id = $param['id'];
		$params[] = $id;
		$set = implode(' = ?, ', $set);
		if ($set) {
			$set .= ' = ? ';
			$query = "UPDATE `" . $this->getTableName('fields') . "` SET " . $set . " WHERE `id` = ?";
			$return = $adb->pquery($query, $params);
		}
		return $return;
	}

	/**
	 * Save sequence field
	 * @param array $sequenceList
	 * @return string/false
	 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
	 */
	public function saveSequence($sequenceList)
	{
		$db = PearDatabase::getInstance();
		$query = "UPDATE `" . $this->getTableName('fields') . "` SET sequence = CASE id ";
		foreach ($sequenceList as $sequence => $id) {
			$query .=' WHEN ' . $id . ' THEN ' . $sequence;
		}
		$query .=' END ';
		$query .= ' WHERE id IN (' . generateQuestionMarks($sequenceList) . ')';
		return $db->pquery($query, array_values($sequenceList));
	}

	/**
	 * Delete fields
	 * @param array $ids
	 * @return string/false
	 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
	 */
	public function delete($param)
	{
		$db = PearDatabase::getInstance();
		$query = "DELETE FROM `" . $this->getTableName('fields') . "` WHERE `id` = ? ";
		$status = $db->pquery($query, [$param['id']]);
		if ($status) {
			$query = "ALTER TABLE `" . $this->getTableName('data') . "` DROP COLUMN `" . $param['column'] . "`;";
			return $db->pquery($query, []);
		}
		return false;
	}

	/**
	 * Getting unique id from invtype
	 * @return int
	 */
	public function getUniqueID($instance)
	{
		$adb = PearDatabase::getInstance();
		$query = "SELECT MAX(id) AS max FROM `" . $this->getTableName('fields') . "` WHERE `invtype` = ? ";
		$result = $adb->pquery($query, [$instance->getName()]);
		return (int) $adb->getSingleValue($result) + 1;
	}

	/**
	 * Getting summary fields name
	 * @return array
	 */
	public function getSummaryFields()
	{
		$summaryFields = [];
		foreach ($this->getFields() as $field) {
			if ($field->isSummary()) {
				$summaryFields[$field->get('columnname')] = $field->get('columnname');
			}
		}
		return $summaryFields;
	}
}
