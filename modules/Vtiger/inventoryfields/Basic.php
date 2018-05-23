<?php

/**
 * Inventory Basic Field Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_Basic_InventoryField extends \App\Base
{
	protected $name = '';
	protected $defaultLabel = '';
	protected $defaultValue = '';
	protected $columnName = '-';
	protected $colSpan = 10;
	protected $dbType = 'string';
	protected $customColumn = [];
	protected $summationValue = false;
	protected $onlyOne = true;
	protected $displayTypeBase = ['LBL_DISPLAYTYPE_ALL' => 0, 'LBL_DISPLAYTYPE_ONLY_DETAIL' => 2, 'LBL_DISPLAYTYPE_HIDDEN' => 5, 'LBL_DISPLAYTYPE_READONLY' => 10];
	protected $blocks = [1];
	protected $fieldDataType = 'inventory';
	protected $params = [];
	protected $maximumLength = 255;

	/**
	 * Getting onlyOne field.
	 *
	 * @return true/false
	 */
	public function isOnlyOne()
	{
		return $this->onlyOne;
	}

	public function getBlocks()
	{
		return $this->blocks;
	}

	/**
	 * Getting database-type of field.
	 *
	 * @return string dbType
	 */
	public function getDBType()
	{
		return $this->dbType;
	}

	/**
	 * Getting all params values.
	 *
	 * @return array
	 */
	public function getParams()
	{
		return $this->params;
	}

	public function getParamsConfig()
	{
		return \App\Json::decode($this->get('params'));
	}

	/**
	 * Getting all values display Type.
	 *
	 * @return array
	 */
	public function displayTypeBase()
	{
		return $this->displayTypeBase;
	}

	public function getColSpan()
	{
		if ($this->has('colspan')) {
			return $this->get('colspan');
		}

		return $this->colSpan;
	}

	public function getRangeValues()
	{
		return $this->maximumLength;
	}

	/**
	 * Getting template name.
	 *
	 * @return string templateName
	 */
	public function getTemplateName($view, $moduleName)
	{
		$tpl = $view . $this->name . '.tpl';
		$filename = 'layouts' . DIRECTORY_SEPARATOR . \App\Layout::getActiveLayout() . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $moduleName . DIRECTORY_SEPARATOR . 'inventoryfields' . DIRECTORY_SEPARATOR . $tpl;
		if (is_file($filename)) {
			return $tpl;
		}
		$filename = 'layouts' . DIRECTORY_SEPARATOR . \App\Layout::getActiveLayout() . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'Vtiger' . DIRECTORY_SEPARATOR . 'inventoryfields' . DIRECTORY_SEPARATOR . $tpl;
		if (is_file($filename)) {
			return $tpl;
		}
		$filename = 'layouts' . DIRECTORY_SEPARATOR . Vtiger_Viewer::getDefaultLayoutName() . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $moduleName . DIRECTORY_SEPARATOR . 'inventoryfields' . DIRECTORY_SEPARATOR . $tpl;
		if (is_file($filename)) {
			return $tpl;
		}
		$filename = 'layouts' . DIRECTORY_SEPARATOR . Vtiger_Viewer::getDefaultLayoutName() . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'Vtiger' . DIRECTORY_SEPARATOR . 'inventoryfields' . DIRECTORY_SEPARATOR . $tpl;
		if (is_file($filename)) {
			return $tpl;
		}

		return $view . 'Base' . '.tpl';
	}

	/**
	 * Getting default label.
	 *
	 * @return string defaultLabel
	 */
	public function getDefaultLabel()
	{
		return $this->defaultLabel;
	}

	/**
	 * Getting field name.
	 *
	 * @return string name
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Getting column name.
	 *
	 * @return string columnName
	 */
	public function getColumnName()
	{
		if ($this->has('columnname')) {
			return $this->get('columnname');
		}

		return $this->columnName;
	}

	/**
	 * Getting column name.
	 *
	 * @return string customColumn
	 */
	public function getCustomColumn()
	{
		return $this->customColumn;
	}

	public function isSummary()
	{
		return $this->summationValue;
	}

	public function getDefaultValue()
	{
		if ($this->has('defaultvalue')) {
			return $this->get('defaultvalue');
		}

		return $this->defaultValue;
	}

	/**
	 * Data field instance initialization.
	 *
	 * @param array $valueArray Array for initialization
	 */
	public function initialize($valueArray)
	{
		$this->setData($valueArray);
	}

	/**
	 * Getting value to display.
	 *
	 * @param type $value
	 *
	 * @return string
	 */
	public function getDisplayValue($value, $rawText = false)
	{
		return \App\Purifier::encodeHtml($value);
	}

	/**
	 * Getting value to display.
	 *
	 * @param type $value
	 *
	 * @return string
	 */
	public function getEditValue($value)
	{
		return $this->getDisplayValue($value);
	}

	/**
	 * Getting value.
	 *
	 * @param type $value
	 *
	 * @return string
	 */
	public function getValue($value)
	{
		if ($value == '') {
			return $this->get('defaultvalue');
		}

		return $value;
	}

	public function isMandatory()
	{
		return true;
	}

	/**
	 * Function to check whether the current field is visible.
	 *
	 * @return bool - true/false
	 */
	public function isVisible()
	{
		if ((int) $this->get('displaytype') == 5) {
			return false;
		}

		return true;
	}

	/**
	 * Function to check whether the current field is editable.
	 *
	 * @return bool - true/false
	 */
	public function isEditable()
	{
		if (!in_array((int) $this->get('displaytype'), [0, 10])) {
			return false;
		}

		return true;
	}

	/**
	 * Function to check whether the current field is editable.
	 *
	 * @return bool - true/false
	 */
	public function isColumnType()
	{
		if (empty($this->columnName) || $this->columnName == '-') {
			return false;
		}

		return true;
	}

	/**
	 * Getting value to display.
	 *
	 * @return array
	 */
	public function modulesValues()
	{
		$modules = Vtiger_Module_Model::getAll([0], [], true);
		foreach ($modules as $module) {
			$modulesNames[] = ['module' => $module->getName(), 'name' => $module->getName(), 'id' => $module->getName()];
		}

		return $modulesNames;
	}

	public function getSummaryValuesFromData($data)
	{
		$sum = 0;
		if (is_array($data)) {
			foreach ($data as $row) {
				$sum += $row[$this->get('columnname')];
			}
		}

		return $sum;
	}

	public function getMapDetail($returInstance = false)
	{
		$inventoryField = Vtiger_InventoryField_Model::getInstance($this->get('module'));
		$fields = $inventoryField->getAutoCompleteFields();
		$name = $this->getColumnName();
		if (isset($fields[$name])) {
			$mapDetail = $fields[$name];
			if ($returInstance) {
				$moduleModel = Vtiger_Module_Model::getInstance($mapDetail['module']);

				return Vtiger_Field_Model::getInstance($mapDetail['field'], $moduleModel);
			} else {
				return $mapDetail;
			}
		}

		return false;
	}

	public function getFieldDataType()
	{
		return $this->fieldDataType;
	}

	/**
	 * Get value from request.
	 *
	 * @param array        $insertData
	 * @param \App\Request $request
	 * @param int          $i
	 *
	 * @return bool
	 */
	public function getValueFromRequest(&$insertData, \App\Request $request, $i)
	{
		$column = $this->getColumnName();
		if (empty($column) || $column === '-' || !$request->has($column . $i)) {
			return false;
		}
		$value = $request->get($column . $i);
		$this->validate($value, $column, true);
		$insertData[$column] = $value;
	}

	/**
	 * Verification of data.
	 *
	 * @param mixed  $value
	 * @param string $columnName
	 * @param bool   $isUserFormat
	 *
	 * @throws \App\Exceptions\Security
	 */
	public function validate($value, $columnName, $isUserFormat = false)
	{
		if (!is_numeric($value) && (is_string($value) && $value !== strip_tags($value))) {
			throw new \App\Exceptions\Security("ERR_ILLEGAL_FIELD_VALUE||$columnName||$value", 406);
		}
		if (App\TextParser::getTextLength($value) > $this->maximumLength) {
			throw new \App\Exceptions\Security("ERR_VALUE_IS_TOO_LONG||$columnName||$value", 406);
		}
	}
}
