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

	/**
	 * Geting database-type of field
	 * @return string dbType
	 */
	public function getDBType()
	{
		return $this->dbType;
	}
	
	public function getColSpan()
	{
		return $this->colSpan;
	}

	/**
	 * Geting template name
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
	 * Geting default label
	 * @return string defaultLabel
	 */
	public function getDefaultLabel()
	{
		return $this->defaultLabel;
	}

	/**
	 * Geting field name
	 * @return string name
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Geting column name
	 * @return string columnName
	 */
	public function getColumnName()
	{
		return $this->columnName;
	}

	/**
	 * Geting column name
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
	 * Geting value to display
	 * @param type $value
	 * @return string
	 */
	public function getDisplayValue($value)
	{
		return $value;
	}

	/**
	 * Geting value to display
	 * @param type $value
	 * @return string
	 */
	public function getEditValue($value)
	{
		return $this->getDisplayValue($value);
	}

	/**
	 * Geting value
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
}
