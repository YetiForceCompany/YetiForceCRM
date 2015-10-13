<?php

/**
 * Inventory Basic Field Class
 * @package YetiForce.Fields
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_Basic_InventoryField extends Vtiger_Base_Model
{

	protected $name = '';
	protected $defaultLabel = '';
	protected $defaultValue = '';
	protected $columnName = '';
	protected $colSpan = 1;
	protected $dbType = 'varchar(100)';
	protected $customColumn = [];
	protected $summationValue = false;
	protected $onlyOne = true;
	protected $displayTypeBase = ['LBL_EDITABLE'=>0, 'LBL_READONLY'=>10];
	protected $blocks = [1];

	/**
	 * Getting onlyOne field
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
	 * Getting database-type of field
	 * @return string dbType
	 */
	public function getDBType()
	{
		return $this->dbType;
	}

	/**
	 * Getting all params values
	 * @return array
	 */
	public function getParams()
	{
		return $this->params;
	}

	/**
	 * Getting all values display Type
	 * @return array
	 */
	public function displayTypeBase()
	{
		return $this->displayTypeBase;
	}

	public function getColSpan()
	{
		if($this->has('colspan'))
			return $this->get('colspan');
		
		return $this->colSpan;
	}

	/**
	 * Getting template name
	 * @return string templateName
	 */
	public function getTemplateName($view, $moduleName)
	{
		$tpl = $view . $this->name . '.tpl';
		$filename = 'layouts' . DIRECTORY_SEPARATOR . 'vlayout' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $moduleName . DIRECTORY_SEPARATOR . 'inventoryfields' . DIRECTORY_SEPARATOR . $tpl;
		if (is_file($filename)) {
			return $tpl;
		}
		$filename = 'layouts' . DIRECTORY_SEPARATOR . 'vlayout' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'Vtiger' . DIRECTORY_SEPARATOR . 'inventoryfields' . DIRECTORY_SEPARATOR . $tpl;
		if (is_file($filename)) {
			return $tpl;
		}
		return $view . 'Base' . '.tpl';
	}

	/**
	 * Getting default label
	 * @return string defaultLabel
	 */
	public function getDefaultLabel()
	{
		return $this->defaultLabel;
	}

	/**
	 * Getting field name
	 * @return string name
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Getting column name
	 * @return string columnName
	 */
	public function getColumnName()
	{
		if($this->has('columnname'))
			return $this->get('columnname');
		return $this->columnName;
	}

	/**
	 * Getting column name
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
		if($this->has('defaultvalue'))
			return $this->get('defaultvalue');
		return $this->defaultValue;
	}

	/**
	 * Data field instance initialization 
	 * @param array $valueArray Array for initialization
	 */
	public function initialize($valueArray)
	{
		$this->setData($valueArray);
	}

	/**
	 * Getting value to display
	 * @param type $value
	 * @return string
	 */
	public function getDisplayValue($value)
	{
		return $value;
	}

	/**
	 * Getting value to display
	 * @param type $value
	 * @return string
	 */
	public function getEditValue($value)
	{
		return $this->getDisplayValue($value);
	}

	/**
	 * Getting value
	 * @param type $value
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

	public function isVisible($data)
	{
		return true;
	}
	
	/**
	 * Getting value to display
	 * @return array
	 */
	public function modulesValues()
	{
		$modules = Vtiger_Module_Model::getAll([0], [], true);
		foreach ($modules AS $module) {
			$modulesNames[] = ['module' => $module->getName(), 'name' => $module->getName(), 'id' => $module->getName()];
		}
		return $modulesNames;
	}

}
