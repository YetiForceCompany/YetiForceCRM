<?php

/**
 * Supplies management fields Model Class
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Supplies_SupField_Model
{

	const DATA_PREFIX = '_sups';
	const FIELDS_PREFIX = '_supfield';

	protected $columns = false;

	/**
	 * Create the name of the Supplies data table
	 * @param string $module Module name
	 * @param string $prefix Prefix table
	 * @return string Table name
	 */
	public function getTableName($module, $type = 'data')
	{
		switch ($type) {
			case 'data':
				$prefix = self::DATA_PREFIX;
				break;
			case 'fields':
				$prefix = self::FIELDS_PREFIX;
				break;
		}
		$moduleName = strtolower($module);
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
	public static function getFields($module, $returnInBlock = false)
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . '| ' . $module);

		$db = PearDatabase::getInstance();
		$supfield = self::getTableName($module, 'fields');
		$result = $db->query("SHOW TABLES LIKE '$supfield'");
		if ($result->rowCount() == 0) {
			return false;
		}
		$result = $db->pquery('SELECT * FROM ' . $supfield . ' WHERE presence = ? ORDER BY sequence', [0]);
		$fields = [];
		while ($row = $db->fetch_array($result)) {
			if (!self::isActiveField($row)) {
				continue;
			}
			if ($returnInBlock) {
				$fields[$row['block']][$row['columnname']] = self::getInstanceFromArray($row);
			} else {
				$fields[$row['columnname']] = self::getInstanceFromArray($row);
			}
		}

		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__);
		return $fields;
	}

	public static function isActiveField($row)
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
	public function getColumns($module)
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . '| ' . $module);
		if ($this->columns) {
			return $this->columns;
		}

		$columns = [];
		foreach ($this->getFields($module) as $field) {
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
	public static function getInstanceFromArray($valueArray)
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . '| ');

		$className = Vtiger_Loader::getComponentClassName('Field', $valueArray['suptype'], 'Supplies');
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

	public static function getCleanInstance()
	{
		$instance = new self();
		return $instance;
	}

	public function isWysiwygType($moduleName)
	{
		$cache = Vtiger_Cache::get('SuppliesisWysiwygType', $moduleName);
		if ($cache) {
			return $cache;
		}
		$return = 0;
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$fieldModel = Vtiger_Field_Model::getInstance('description', $moduleModel);
		if ($fieldModel && $fieldModel->get('uitype') == '300') {
			$return = 1;
		}
		Vtiger_Cache::set('SuppliesisWysiwygType', $moduleName, $return);
		return $return;
	}
}
